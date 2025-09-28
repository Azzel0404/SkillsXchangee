@extends('layouts.chat')

@section('content')
<script>
    // Initialize global variables for the chat session
    window.currentUserId = parseInt('{{ auth()->id() }}');
    window.tradeId = parseInt('{{ $trade->id }}');
    window.authUserId = parseInt('{{ Auth::id() }}');
    window.partnerId = parseInt('{{ $partner->id }}');
    window.partnerName = '{{ addslashes(($partner->firstname ?? "Unknown") . " " . ($partner->lastname ?? "User")) }}';
    window.initialMessageCount = parseInt('{{ $messages->count() }}');
    
    // Enhanced Video Call Integration
    let firebaseVideoCall = null;
    let videoCallUI = null;
    let videoCallState = {
        isActive: false,
        isConnected: false,
        isInitiator: false,
        callId: null,
        partnerId: null,
        localStream: null,
        remoteStream: null,
        peerConnection: null,
        startTime: null,
        timer: null
    };
    
    // Initialize Firebase video call integration
    async function initializeFirebaseVideoCall() {
        try {
            console.log('🔥 Initializing enhanced Firebase video call integration...');
            
            // Import Firebase modules
            const { initializeApp } = await import('/firebase/app.js');
            const { getDatabase, ref, set, onValue, off, remove, push } = await import('/firebase/database.js');
            const { firebaseConfig } = await import('/firebase-config.js');
            
            // Initialize Firebase
            const app = initializeApp(firebaseConfig);
            const database = getDatabase(app);
            
            // Create enhanced video call UI
            videoCallUI = new EnhancedVideoCallUI('video-call-container', {
                showLocalVideo: true,
                showRemoteVideo: true,
                enableControls: true,
                enableStatus: true,
                enableTimer: true,
                enableParticipantInfo: true
            });
            
            // Bind UI events
            videoCallUI.onAnswerCall = () => answerVideoCall();
            videoCallUI.onDeclineCall = () => declineVideoCall();
            videoCallUI.onEndCall = () => endVideoCall();
            videoCallUI.onMuteToggle = (isMuted) => toggleMute(isMuted);
            videoCallUI.onVideoToggle = (isVideoOff) => toggleVideo(isVideoOff);
            videoCallUI.onScreenShareToggle = (isScreenSharing) => toggleScreenShare(isScreenSharing);
            videoCallUI.onCloseCall = () => closeVideoCall();
            videoCallUI.onMinimizeCall = () => minimizeVideoCall();
            
            // Create Firebase video call instance
            firebaseVideoCall = new FirebaseVideoIntegration({
                userId: window.currentUserId,
                tradeId: window.tradeId,
                partnerId: window.partnerId,
                onCallReceived: (call) => {
                    console.log('📞 Incoming call received:', call);
                    videoCallUI.showIncomingCall(call.fromUserName || window.partnerName);
                    videoCallState.incomingOffer = call.offer;
                    videoCallState.partnerId = call.fromUserId;
                    videoCallState.callId = call.callId;
                },
                onCallAnswered: (stream) => {
                    console.log('📞 Call answered, remote stream received');
                    videoCallState.remoteStream = stream;
                    videoCallUI.setRemoteStream(stream);
                    videoCallUI.setConnected(true);
                    videoCallState.isConnected = true;
                },
                onCallEnded: () => {
                    console.log('📞 Call ended');
                    endVideoCall();
                },
                onConnectionStateChange: (state) => {
                    console.log('🔗 Connection state changed:', state);
                    videoCallUI.setConnected(state === 'connected');
                    if (state === 'connected') {
                        videoCallState.isConnected = true;
                    } else if (state === 'failed') {
                        videoCallState.isConnected = false;
                    }
                },
                onStatusUpdate: (status) => {
                    console.log('📊 Status update:', status);
                    // Update UI status if needed
                },
                onParticipantUpdate: (participants) => {
                    console.log('👥 Participants update:', participants);
                    // Update participant info if needed
                },
                onError: (error) => {
                    console.error('❌ Firebase video call error:', error);
                    showVideoCallError('Video call error: ' + error.message);
                },
                onLog: (message, type) => {
                    console.log(`[FirebaseVideoCall] ${message}`);
                }
            });
            
            // Initialize Firebase
            const success = await firebaseVideoCall.initialize();
            if (success) {
                console.log('✅ Enhanced Firebase video call integration initialized successfully');
            } else {
                console.error('❌ Failed to initialize Firebase video call integration');
            }
            
        } catch (error) {
            console.error('❌ Error initializing Firebase video call:', error);
            showVideoCallError('Failed to initialize video calling: ' + error.message);
        }
    }
    
    // Start video call
    async function startVideoCall() {
        try {
            console.log('📞 Starting enhanced video call...');
            
            if (!firebaseVideoCall) {
                throw new Error('Firebase video call not initialized');
            }
            
            // Show video call UI
            document.getElementById('video-call-container').style.display = 'block';
            document.getElementById('startVideoCallBtn').style.display = 'none';
            
            // Start the call
            const success = await firebaseVideoCall.startCall(window.partnerId);
            
            if (success) {
                videoCallState.isActive = true;
                videoCallState.isInitiator = true;
                
                // Set local stream in UI
                if (firebaseVideoCall.localStream) {
                    videoCallUI.setLocalStream(firebaseVideoCall.localStream);
                }
            } else {
                throw new Error('Failed to start video call');
            }
            
        } catch (error) {
            console.error('❌ Error starting video call:', error);
            showVideoCallError('Failed to start video call: ' + error.message);
            endVideoCall();
        }
    }
    
    // Answer video call
    async function answerVideoCall() {
        try {
            console.log('📞 Answering enhanced video call...');
            
            if (!firebaseVideoCall || !videoCallState.incomingOffer) {
                throw new Error('Firebase video call not initialized or no incoming offer');
            }
            
            // Hide incoming call modal
            videoCallUI.hideIncomingCall();
            
            // Answer the call
            const success = await firebaseVideoCall.answerCall(videoCallState.incomingOffer);
            
            if (success) {
                videoCallState.isActive = true;
                videoCallState.isInitiator = false;
                
                // Set local stream in UI
                if (firebaseVideoCall.localStream) {
                    videoCallUI.setLocalStream(firebaseVideoCall.localStream);
                }
            } else {
                throw new Error('Failed to answer video call');
            }
            
        } catch (error) {
            console.error('❌ Error answering video call:', error);
            showVideoCallError('Failed to answer video call: ' + error.message);
            endVideoCall();
        }
    }
    
    // Decline video call
    function declineVideoCall() {
        console.log('📞 Declining video call...');
        videoCallUI.hideIncomingCall();
        endVideoCall();
    }
    
    // End video call
    async function endVideoCall() {
        try {
            console.log('📞 Ending enhanced video call...');
            
            if (firebaseVideoCall) {
                await firebaseVideoCall.endCall();
            }
            
            // Reset state
            videoCallState.isActive = false;
            videoCallState.isConnected = false;
            videoCallState.isInitiator = false;
            videoCallState.callId = null;
            videoCallState.partnerId = null;
            videoCallState.localStream = null;
            videoCallState.remoteStream = null;
            videoCallState.peerConnection = null;
            
            // Update UI
            videoCallUI.setConnected(false);
            document.getElementById('video-call-container').style.display = 'none';
            document.getElementById('startVideoCallBtn').style.display = 'block';
            
        } catch (error) {
            console.error('❌ Error ending video call:', error);
        }
    }
    
    // Close video call
    function closeVideoCall() {
        endVideoCall();
    }
    
    // Minimize video call
    function minimizeVideoCall() {
        // Implementation for minimizing video call
        console.log('📞 Minimizing video call...');
    }
    
    // Toggle mute
    function toggleMute(isMuted = null) {
        if (firebaseVideoCall) {
            const newMutedState = isMuted !== null ? isMuted : firebaseVideoCall.toggleMute();
            videoCallUI.state.isMuted = newMutedState;
            videoCallUI.updateLocalStatus();
            console.log(`🔇 Audio ${newMutedState ? 'muted' : 'unmuted'}`);
        }
    }
    
    // Toggle video
    function toggleVideo(isVideoOff = null) {
        if (firebaseVideoCall) {
            const newVideoOffState = isVideoOff !== null ? isVideoOff : firebaseVideoCall.toggleVideo();
            videoCallUI.state.isVideoOff = newVideoOffState;
            videoCallUI.updateLocalStatus();
            console.log(`📹 Video ${newVideoOffState ? 'disabled' : 'enabled'}`);
        }
    }
    
    // Toggle screen share
    function toggleScreenShare(isScreenSharing = null) {
        if (firebaseVideoCall) {
            // Implementation for screen sharing
            const newScreenSharingState = isScreenSharing !== null ? isScreenSharing : !videoCallUI.state.isScreenSharing;
            videoCallUI.state.isScreenSharing = newScreenSharingState;
            videoCallUI.updateLocalStatus();
            console.log(`🖥️ Screen sharing ${newScreenSharingState ? 'enabled' : 'disabled'}`);
        }
    }
    
    // Show video call error
    function showVideoCallError(message) {
        console.error('📞 Video Call Error:', message);
        
        // Show error notification
        const notification = document.createElement('div');
        notification.className = 'video-call-error-notification';
        notification.innerHTML = `
            <div class="error-content">
                <div class="error-icon">⚠️</div>
                <div class="error-message">${message}</div>
                <button class="error-close" onclick="this.parentElement.parentElement.remove()">✕</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initializing enhanced Firebase video call integration...');
        
        // Initialize Firebase video call
        initializeFirebaseVideoCall();
        
        // Initialize notification service
        if (typeof NotificationService !== 'undefined') {
            window.notificationService = new NotificationService();
            if (window.notificationService.requestPermission) {
                window.notificationService.requestPermission();
            }
            console.log('✅ Notification service initialized');
        } else {
            console.warn('⚠️ NotificationService not available');
        }
    });
</script>

<!-- Enhanced Video Call Container -->
<div id="video-call-container" class="enhanced-video-call-container" style="display: none;">
    <!-- Video call UI will be rendered here by EnhancedVideoCallUI -->
</div>

<!-- Start Video Call Button -->
<button id="startVideoCallBtn" class="enhanced-start-call-btn" onclick="startVideoCall()">
    <div class="btn-content">
        <div class="btn-icon">📞</div>
        <div class="btn-text">
            <div class="btn-title">Start Video Call</div>
            <div class="btn-subtitle">with {{ $partner->firstname }}</div>
        </div>
    </div>
</button>

<!-- Include Enhanced UI and Firebase Integration -->
<script type="module" src="/enhanced-video-call-ui.js"></script>
<script type="module" src="/firebase-video-integration.js"></script>

<!-- Enhanced Video Call Styles -->
<style>
    .enhanced-video-call-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 1000;
    }

    .enhanced-start-call-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 16px;
        padding: 20px 30px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        margin: 20px 0;
        min-width: 200px;
    }

    .enhanced-start-call-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    }

    .enhanced-start-call-btn:active {
        transform: translateY(-1px);
    }

    .btn-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .btn-icon {
        font-size: 24px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }

    .btn-text {
        text-align: left;
    }

    .btn-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .btn-subtitle {
        font-size: 14px;
        opacity: 0.9;
    }

    .video-call-error-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #f44336;
        color: white;
        border-radius: 12px;
        padding: 15px 20px;
        box-shadow: 0 8px 25px rgba(244, 67, 54, 0.3);
        z-index: 2000;
        animation: slideInRight 0.3s ease;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .error-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .error-icon {
        font-size: 20px;
    }

    .error-message {
        flex: 1;
        font-size: 14px;
        font-weight: 500;
    }

    .error-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 16px;
        padding: 4px;
        border-radius: 4px;
        transition: background 0.2s ease;
    }

    .error-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .enhanced-start-call-btn {
            padding: 15px 20px;
            min-width: 150px;
        }

        .btn-icon {
            font-size: 20px;
        }

        .btn-title {
            font-size: 14px;
        }

        .btn-subtitle {
            font-size: 12px;
        }
    }
</style>

@endsection