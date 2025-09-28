/**
 * Firebase Video Call Integration for SkillsXchangee
 * Replaces WebSocket/Pusher with Firebase Realtime Database
 */

class FirebaseVideoIntegration {
    constructor(options = {}) {
        this.userId = options.userId;
        this.tradeId = options.tradeId;
        this.partnerId = options.partnerId;
        this.callId = null;
        this.isInitiator = false;
        this.isConnected = false;
        this.isActive = false;
        
        // Firebase setup
        this.app = null;
        this.database = null;
        this.roomRef = null;
        this.callRef = null;
        
        // WebRTC state
        this.localStream = null;
        this.remoteStream = null;
        this.peerConnection = null;
        this.startTime = null;
        this.timer = null;
        
        // Callbacks
        this.onCallReceived = options.onCallReceived || (() => {});
        this.onCallAnswered = options.onCallAnswered || (() => {});
        this.onCallEnded = options.onCallEnded || (() => {});
        this.onConnectionStateChange = options.onConnectionStateChange || (() => {});
        this.onError = options.onError || (() => {});
        this.onLog = options.onLog || (() => {});
        this.onStatusUpdate = options.onStatusUpdate || (() => {});
        this.onParticipantUpdate = options.onParticipantUpdate || (() => {});
        
        // WebRTC Configuration
        this.config = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun.relay.metered.ca:80' },
                {
                    urls: 'turn:asia.relay.metered.ca:80',
                    username: '0582eeabe15281e17e922394',
                    credential: 'g7fjNoaIyTpLnkaf'
                }
            ],
            iceCandidatePoolSize: 10,
            bundlePolicy: 'max-bundle',
            rtcpMuxPolicy: 'require',
            iceTransportPolicy: 'all'
        };
    }
    
    log(message, type = 'info') {
        console.log(`[FirebaseVideoIntegration] ${message}`);
        this.onLog(message, type);
    }
    
    // Initialize Firebase
    async initialize() {
        try {
            this.log('🔥 Initializing Firebase video integration...');
            
            // Import Firebase modules
            const { initializeApp } = await import('./firebase/app.js');
            const { getDatabase, ref, set, onValue, off, remove, push } = await import('./firebase/database.js');
            const { firebaseConfig } = await import('./firebase-config.js');
            
            // Initialize Firebase
            this.app = initializeApp(firebaseConfig);
            this.database = getDatabase(this.app);
            this.roomRef = ref(this.database, `rooms/trade_${this.tradeId}`);
            
            // Join the room
            await this.joinRoom();
            
            // Setup Firebase listeners
            this.setupFirebaseListeners();
            
            this.log('✅ Firebase video integration initialized successfully');
            return true;
            
        } catch (error) {
            this.log(`❌ Firebase initialization error: ${error.message}`, 'error');
            this.onError(error);
            return false;
        }
    }
    
    // Join Firebase room
    async joinRoom() {
        const { set } = await import('./firebase/database.js');
        const userRef = ref(this.database, `rooms/trade_${this.tradeId}/users/${this.userId}`);
        await set(userRef, {
            userId: this.userId,
            status: 'online',
            joinedAt: Date.now()
        });
        this.log(`Joined room: trade_${this.tradeId}`);
    }
    
    // Setup Firebase listeners for call events
    setupFirebaseListeners() {
        const { onValue } = await import('./firebase/database.js');
        const callsRef = ref(this.database, `rooms/trade_${this.tradeId}/calls`);
        
        onValue(callsRef, (snapshot) => {
            const calls = snapshot.val();
            if (!calls) return;
            
            // Check for incoming calls
            Object.keys(calls).forEach(callId => {
                const call = calls[callId];
                
                // Handle incoming offer
                if (call.toUserId === this.userId && call.type === 'offer' && !this.isInitiator) {
                    this.log('📞 Incoming call received');
                    this.handleIncomingCall(call);
                }
                
                // Handle incoming answer
                if (call.fromUserId === this.userId && call.type === 'answer' && this.isInitiator) {
                    this.log('📞 Call answered');
                    this.handleCallAnswer(call.answer);
                }
                
                // Handle ICE candidates
                if (call.type === 'ice-candidate') {
                    if ((call.toUserId === this.userId && call.fromUserId !== this.userId) ||
                        (call.fromUserId === this.userId && call.toUserId !== this.userId)) {
                        this.handleIceCandidate(call.candidate);
                    }
                }
                
                // Handle call end
                if (call.type === 'end-call' && 
                    (call.toUserId === this.userId || call.fromUserId === this.userId)) {
                    this.log('📞 Call ended by partner');
                    this.handleCallEnd();
                }
            });
        });
    }
    
    // Start a video call
    async startCall(partnerId) {
        try {
            this.log('📞 Starting video call...');
            this.partnerId = partnerId;
            this.callId = `call_${Date.now()}_${this.userId}`;
            this.isInitiator = true;
            
            // Update status
            this.onStatusUpdate?.('Getting camera access...');
            
            // Get user media
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1280, height: 720 },
                audio: { echoCancellation: true, noiseSuppression: true }
            });
            
            this.log('✅ Local stream obtained');
            this.onStatusUpdate?.('Setting up connection...');
            
            // Create peer connection
            await this.createPeerConnection();
            
            // Add local stream to peer connection
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });
            
            // Create offer
            this.onStatusUpdate?.('Creating offer...');
            const offer = await this.peerConnection.createOffer();
            await this.peerConnection.setLocalDescription(offer);
            
            this.log('✅ Offer created');
            this.onStatusUpdate?.('Sending offer...');
            
            // Send offer via Firebase
            await this.sendOffer(offer);
            
            this.isActive = true;
            this.startCallTimer();
            
            this.log('✅ Video call initiated successfully');
            this.onStatusUpdate?.('Call initiated, waiting for answer...');
            return true;
            
        } catch (error) {
            this.log(`❌ Error starting call: ${error.message}`, 'error');
            this.onStatusUpdate?.(`Error: ${error.message}`);
            this.onError(error);
            return false;
        }
    }
    
    // Answer a video call
    async answerCall(offer) {
        try {
            this.log('📞 Answering video call...');
            this.isInitiator = false;
            
            // Get user media
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1280, height: 720 },
                audio: { echoCancellation: true, noiseSuppression: true }
            });
            
            this.log('✅ Local stream obtained');
            
            // Create peer connection
            await this.createPeerConnection();
            
            // Add local stream to peer connection
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });
            
            // Set remote description
            await this.peerConnection.setRemoteDescription(offer);
            this.log('✅ Remote description set');
            
            // Create answer
            const answer = await this.peerConnection.createAnswer();
            await this.peerConnection.setLocalDescription(answer);
            
            this.log('✅ Answer created');
            
            // Send answer via Firebase
            await this.sendAnswer(answer);
            
            this.isActive = true;
            this.startCallTimer();
            
            this.log('✅ Video call answered successfully');
            return true;
            
        } catch (error) {
            this.log(`❌ Error answering call: ${error.message}`, 'error');
            this.onError(error);
            return false;
        }
    }
    
    // Create WebRTC peer connection
    async createPeerConnection() {
        this.log('🔗 Creating peer connection...');
        
        this.peerConnection = new RTCPeerConnection(this.config);
        
        // Handle ICE candidates
        this.peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                this.log('📡 ICE candidate generated');
                this.sendIceCandidate(event.candidate);
            }
        };
        
        // Handle remote stream
        this.peerConnection.ontrack = (event) => {
            this.log('📹 Remote stream received');
            this.remoteStream = event.streams[0];
            this.onCallAnswered(this.remoteStream);
        };
        
        // Handle connection state changes
        this.peerConnection.onconnectionstatechange = () => {
            const state = this.peerConnection.connectionState;
            this.log(`🔗 Connection state: ${state}`);
            this.onConnectionStateChange(state);
            
            if (state === 'connected') {
                this.isConnected = true;
                this.log('✅ Call connected successfully!');
            } else if (state === 'failed') {
                this.isConnected = false;
                this.log('❌ Connection failed', 'error');
            }
        };
        
        // Handle ICE connection state changes
        this.peerConnection.oniceconnectionstatechange = () => {
            const state = this.peerConnection.iceConnectionState;
            this.log(`🧊 ICE connection state: ${state}`);
            
            if (state === 'failed') {
                this.log('⚠️ ICE connection failed, attempting restart...', 'warning');
                this.peerConnection.restartIce();
            }
        };
        
        this.log('✅ Peer connection created');
    }
    
    // Send offer via Firebase
    async sendOffer(offer) {
        const { set } = await import('./firebase/database.js');
        const callRef = ref(this.database, `rooms/trade_${this.tradeId}/calls/${this.callId}`);
        await set(callRef, {
            type: 'offer',
            fromUserId: this.userId,
            toUserId: this.partnerId,
            offer: offer,
            callId: this.callId,
            timestamp: Date.now()
        });
        this.log('📤 Offer sent via Firebase');
    }
    
    // Send answer via Firebase
    async sendAnswer(answer) {
        const { set } = await import('./firebase/database.js');
        const callRef = ref(this.database, `rooms/trade_${this.tradeId}/calls/${this.callId}`);
        await set(callRef, {
            type: 'answer',
            fromUserId: this.userId,
            toUserId: this.partnerId,
            answer: answer,
            callId: this.callId,
            timestamp: Date.now()
        });
        this.log('📤 Answer sent via Firebase');
    }
    
    // Send ICE candidate via Firebase
    async sendIceCandidate(candidate) {
        const { set } = await import('./firebase/database.js');
        const callRef = ref(this.database, `rooms/trade_${this.tradeId}/calls/${this.callId}_ice_${Date.now()}`);
        await set(callRef, {
            type: 'ice-candidate',
            fromUserId: this.userId,
            toUserId: this.partnerId,
            candidate: candidate,
            callId: this.callId,
            timestamp: Date.now()
        });
        this.log('📤 ICE candidate sent via Firebase');
    }
    
    // Handle incoming call
    async handleIncomingCall(call) {
        this.log('📞 Handling incoming call...');
        this.callId = call.callId;
        this.partnerId = call.fromUserId;
        
        // Show incoming call notification
        this.onCallReceived(call);
    }
    
    // Handle call answer
    async handleCallAnswer(answer) {
        this.log('📞 Handling call answer...');
        
        if (!this.peerConnection) {
            this.log('❌ No peer connection to handle answer', 'error');
            return;
        }
        
        await this.peerConnection.setRemoteDescription(answer);
        this.log('✅ Remote answer set');
    }
    
    // Handle ICE candidate
    async handleIceCandidate(candidate) {
        this.log('📡 Handling ICE candidate...');
        
        if (!this.peerConnection) {
            this.log('❌ No peer connection to handle ICE candidate', 'error');
            return;
        }
        
        await this.peerConnection.addIceCandidate(candidate);
        this.log('✅ ICE candidate added');
    }
    
    // Handle call end
    handleCallEnd() {
        this.log('📞 Handling call end...');
        this.endCall();
        this.onCallEnded();
    }
    
    // End the call
    async endCall() {
        this.log('📞 Ending video call...');
        
        // Send end call signal via Firebase
        if (this.callId && this.partnerId) {
            const { set } = await import('./firebase/database.js');
            const callRef = ref(this.database, `rooms/trade_${this.tradeId}/calls/${this.callId}_end`);
            await set(callRef, {
                type: 'end-call',
                fromUserId: this.userId,
                toUserId: this.partnerId,
                callId: this.callId,
                timestamp: Date.now()
            });
        }
        
        // Stop local stream
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }
        
        // Close peer connection
        if (this.peerConnection) {
            this.peerConnection.close();
            this.peerConnection = null;
        }
        
        // Stop timer
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
        
        // Reset state
        this.isConnected = false;
        this.isInitiator = false;
        this.isActive = false;
        this.callId = null;
        this.partnerId = null;
        this.remoteStream = null;
        
        this.log('✅ Call ended');
    }
    
    // Start call timer
    startCallTimer() {
        this.startTime = Date.now();
        this.timer = setInterval(() => {
            const elapsed = Date.now() - this.startTime;
            const minutes = Math.floor(elapsed / 60000);
            const seconds = Math.floor((elapsed % 60000) / 1000);
            const timeString = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            
            // Update timer display
            const timerElement = document.getElementById('call-timer');
            if (timerElement) {
                timerElement.textContent = timeString;
            }
        }, 1000);
    }
    
    // Toggle mute
    toggleMute() {
        if (this.localStream) {
            const audioTrack = this.localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                this.log(`🔇 Audio ${audioTrack.enabled ? 'unmuted' : 'muted'}`);
                return !audioTrack.enabled;
            }
        }
        return false;
    }
    
    // Toggle video
    toggleVideo() {
        if (this.localStream) {
            const videoTrack = this.localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = !videoTrack.enabled;
                this.log(`📹 Video ${videoTrack.enabled ? 'enabled' : 'disabled'}`);
                return !videoTrack.enabled;
            }
        }
        return false;
    }
    
    // Get connection state
    getConnectionState() {
        return this.peerConnection ? this.peerConnection.connectionState : 'disconnected';
    }
    
    // Check if call is active
    isCallActive() {
        return this.isActive && this.peerConnection && this.peerConnection.connectionState === 'connected';
    }
    
    // Cleanup
    cleanup() {
        if (this.roomRef) {
            const { off } = await import('./firebase/database.js');
            off(this.roomRef);
        }
        this.endCall();
    }
}

// Export for use in your application
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FirebaseVideoIntegration;
} else {
    window.FirebaseVideoIntegration = FirebaseVideoIntegration;
}
