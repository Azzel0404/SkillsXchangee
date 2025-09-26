<!-- Video Call Interface - Fixed Version -->
<div id="video-call-container"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; color: white;">
    <div
        style="position: absolute; top: 20px; left: 20px; right: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 id="call-status">Initializing...</h3>
            <p id="call-timer">00:00</p>
        </div>
        <button id="end-call-btn" onclick="endVideoCall()"
            style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">End
            Call</button>
    </div>

    <div style="display: flex; justify-content: center; align-items: center; height: 100%; gap: 20px;">
        <!-- Remote Video -->
        <div style="flex: 1; max-width: 60%; position: relative;">
            <video id="remote-video" autoplay playsinline
                style="width: 100%; height: auto; border-radius: 10px; background: #000;"></video>
            <div id="remote-status"
                style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 5px;">
                Waiting...</div>
        </div>

        <!-- Local Video -->
        <div style="position: absolute; top: 80px; right: 20px; width: 200px; height: 150px;">
            <video id="local-video" autoplay muted playsinline
                style="width: 100%; height: 100%; border-radius: 10px; background: #000; transform: scaleX(-1);"></video>
            <div id="local-status"
                style="position: absolute; top: 5px; left: 5px; background: rgba(0,0,0,0.7); padding: 2px 5px; border-radius: 3px; font-size: 12px;">
                You</div>
        </div>
    </div>

    <!-- Controls -->
    <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px;">
        <button id="mute-btn" onclick="toggleMute()"
            style="background: #6c757d; color: white; border: none; padding: 10px; border-radius: 50%; cursor: pointer; width: 50px; height: 50px;">🎤</button>
        <button id="video-btn" onclick="toggleVideo()"
            style="background: #6c757d; color: white; border: none; padding: 10px; border-radius: 50%; cursor: pointer; width: 50px; height: 50px;">📹</button>
    </div>
</div>

<script>
    // Video Call State Management
let videoCallState = {
    isActive: false,
    isInitiator: false,
    isMuted: false,
    isVideoOff: false,
    callId: null,
    partnerId: null,
    startTime: null,
    timer: null,
    peerConnection: null,
    localStream: null,
    remoteStream: null
};

// Pusher Configuration
const PUSHER_KEY = '{{ env("VITE_PUSHER_APP_KEY", "5c02e54d01ca577ae77e") }}';
const PUSHER_CLUSTER = '{{ env("VITE_PUSHER_APP_CLUSTER", "ap1") }}';
const METERED_API_KEY = '511852cda421697270ed9af8b089038b39a7';
const METERED_API_URL = 'https://skillxchange.metered.live/api/v1/turn/credentials';

// Initialize Pusher
let pusher = null;
let channel = null;

function initializePusher() {
    if (typeof window.Pusher === 'undefined') {
        console.error('Pusher not available');
        return false;
    }
    
    pusher = new Pusher(PUSHER_KEY, {
        cluster: PUSHER_CLUSTER,
        encrypted: true,
        authEndpoint: '/broadcasting/auth'
    });
    
    channel = pusher.subscribe('private-trade.{{ $trade->id }}');
    
    // Connection monitoring
    pusher.connection.bind('connected', () => {
        console.log('✅ Pusher connected for video calls');
        updateCallStatus('Connected to signaling server');
    });
    
    pusher.connection.bind('disconnected', () => {
        console.log('❌ Pusher disconnected');
        updateCallStatus('Connection lost');
    });
    
    pusher.connection.bind('error', (error) => {
        console.error('Pusher error:', error);
        updateCallStatus('Connection error');
    });
    
    // Event listeners
    channel.bind('video-call-offer', handleVideoCallOffer);
    channel.bind('video-call-answer', handleVideoCallAnswer);
    channel.bind('video-call-ice-candidate', handleIceCandidate);
    channel.bind('video-call-end', handleVideoCallEnd);
    
    return true;
}

// Start video call
async function startVideoCall() {
    console.log('🎥 Starting video call...');
    
    if (!initializePusher()) {
        alert('Video call not available. Please refresh the page.');
        return;
    }
    
    try {
        // Get partner ID
        const partnerId = getPartnerId();
        if (!partnerId) {
            alert('No partner found for this trade.');
            return;
        }
        
        videoCallState.isInitiator = true;
        videoCallState.partnerId = partnerId;
        videoCallState.callId = generateCallId();
        
        // Get user media
        videoCallState.localStream = await navigator.mediaDevices.getUserMedia({
            video: { width: 1280, height: 720 },
            audio: { echoCancellation: true, noiseSuppression: true }
        });
        
        // Setup local video
        const localVideo = document.getElementById('local-video');
        localVideo.srcObject = videoCallState.localStream;
        
        // Create peer connection
        await createPeerConnection();
        
        // Show video call interface
        document.getElementById('video-call-container').style.display = 'block';
        document.getElementById('videoCallBtn').style.display = 'none';
        
        // Create offer
        const offer = await videoCallState.peerConnection.createOffer();
        await videoCallState.peerConnection.setLocalDescription(offer);
        
        // Send offer
        await sendVideoCallOffer(offer);
        
        updateCallStatus('Calling...');
        videoCallState.isActive = true;
        startCallTimer();
        
    } catch (error) {
        console.error('Error starting video call:', error);
        alert('Failed to start video call: ' + error.message);
        endVideoCall();
    }
}

// Create peer connection
async function createPeerConnection() {
    const iceServers = await fetchTurnCredentials();
    
    const configuration = {
        iceServers: iceServers,
        iceCandidatePoolSize: 10,
        bundlePolicy: 'max-bundle',
        rtcpMuxPolicy: 'require',
        iceTransportPolicy: 'all'
    };
    
    videoCallState.peerConnection = new RTCPeerConnection(configuration);
    
    // Add local stream tracks
    if (videoCallState.localStream) {
        videoCallState.localStream.getTracks().forEach(track => {
            videoCallState.peerConnection.addTrack(track, videoCallState.localStream);
        });
    }
    
    // Handle ICE candidates
    videoCallState.peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            console.log('📡 Sending ICE candidate');
            sendIceCandidate(event.candidate);
        }
    };
    
    // Handle remote stream
    videoCallState.peerConnection.ontrack = (event) => {
        console.log('📹 Received remote stream');
        videoCallState.remoteStream = event.streams[0];
        const remoteVideo = document.getElementById('remote-video');
        remoteVideo.srcObject = videoCallState.remoteStream;
        updateCallStatus('Connected');
    };
    
    // Handle connection state changes
    videoCallState.peerConnection.onconnectionstatechange = () => {
        console.log('Connection state:', videoCallState.peerConnection.connectionState);
        switch (videoCallState.peerConnection.connectionState) {
            case 'connected':
                updateCallStatus('Connected');
                break;
            case 'failed':
                updateCallStatus('Connection failed');
                break;
            case 'disconnected':
                updateCallStatus('Connection lost');
                break;
        }
    };
    
    // Handle ICE connection state changes
    videoCallState.peerConnection.oniceconnectionstatechange = () => {
        console.log('ICE connection state:', videoCallState.peerConnection.iceConnectionState);
        if (videoCallState.peerConnection.iceConnectionState === 'failed') {
            console.log('ICE connection failed, restarting...');
            videoCallState.peerConnection.restartIce();
        }
    };
}

// Fetch TURN credentials
async function fetchTurnCredentials() {
    try {
        console.log('🔄 Fetching TURN credentials...');
        const response = await fetch(`${METERED_API_URL}?apiKey=${METERED_API_KEY}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const iceServers = await response.json();
        console.log('✅ TURN credentials fetched:', iceServers.length, 'servers');
        return iceServers;
        
    } catch (error) {
        console.error('❌ Error fetching TURN credentials:', error);
        // Fallback servers
        return [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun.relay.metered.ca:80' }
        ];
    }
}

// Send video call offer
async function sendVideoCallOffer(offer) {
    try {
        const response = await fetch(`/chat/{{ $trade->id }}/video-call/offer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                offer: offer,
                callId: videoCallState.callId
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        console.log('✅ Offer sent successfully');
        
    } catch (error) {
        console.error('❌ Error sending offer:', error);
        throw error;
    }
}

// Send video call answer
async function sendVideoCallAnswer(answer) {
    try {
        const response = await fetch(`/chat/{{ $trade->id }}/video-call/answer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                answer: answer,
                callId: videoCallState.callId,
                toUserId: videoCallState.partnerId
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        console.log('✅ Answer sent successfully');
        
    } catch (error) {
        console.error('❌ Error sending answer:', error);
        throw error;
    }
}

// Send ICE candidate
async function sendIceCandidate(candidate) {
    try {
        const response = await fetch(`/chat/{{ $trade->id }}/video-call/ice-candidate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                candidate: candidate,
                callId: videoCallState.callId,
                toUserId: videoCallState.partnerId
            })
        });
        
        if (!response.ok) {
            console.warn('ICE candidate send failed:', response.status);
        }
        
    } catch (error) {
        console.warn('ICE candidate send error:', error);
    }
}

// Handle incoming offer
async function handleVideoCallOffer(data) {
    console.log('📞 Received video call offer:', data);
    
    if (data.fromUserId === {{ auth()->id() }}) {
        console.log('Ignoring offer from self');
        return;
    }
    
    try {
        videoCallState.isInitiator = false;
        videoCallState.partnerId = data.fromUserId;
        videoCallState.callId = data.callId;
        
        // Get user media
        videoCallState.localStream = await navigator.mediaDevices.getUserMedia({
            video: { width: 1280, height: 720 },
            audio: { echoCancellation: true, noiseSuppression: true }
        });
        
        // Setup local video
        const localVideo = document.getElementById('local-video');
        localVideo.srcObject = videoCallState.localStream;
        
        // Create peer connection
        await createPeerConnection();
        
        // Show video call interface
        document.getElementById('video-call-container').style.display = 'block';
        document.getElementById('videoCallBtn').style.display = 'none';
        
        // Set remote description
        await videoCallState.peerConnection.setRemoteDescription(data.offer);
        
        // Create answer
        const answer = await videoCallState.peerConnection.createAnswer();
        await videoCallState.peerConnection.setLocalDescription(answer);
        
        // Send answer
        await sendVideoCallAnswer(answer);
        
        updateCallStatus('Answering...');
        videoCallState.isActive = true;
        startCallTimer();
        
    } catch (error) {
        console.error('Error handling offer:', error);
        alert('Failed to answer call: ' + error.message);
        endVideoCall();
    }
}

// Handle incoming answer
async function handleVideoCallAnswer(data) {
    console.log('📞 Received video call answer:', data);
    
    if (data.toUserId !== {{ auth()->id() }}) {
        console.log('Answer not for this user');
        return;
    }
    
    try {
        await videoCallState.peerConnection.setRemoteDescription(data.answer);
        updateCallStatus('Connected');
        
    } catch (error) {
        console.error('Error handling answer:', error);
    }
}

// Handle ICE candidate
async function handleIceCandidate(data) {
    console.log('📞 Received ICE candidate:', data);
    
    if (data.toUserId !== {{ auth()->id() }}) {
        console.log('ICE candidate not for this user');
        return;
    }
    
    try {
        await videoCallState.peerConnection.addIceCandidate(data.candidate);
        
    } catch (error) {
        console.error('Error handling ICE candidate:', error);
    }
}

// Handle call end
function handleVideoCallEnd(data) {
    console.log('📞 Video call ended:', data);
    endVideoCall();
}

// End video call
function endVideoCall() {
    console.log('📞 Ending video call...');
    
    // Stop local stream
    if (videoCallState.localStream) {
        videoCallState.localStream.getTracks().forEach(track => track.stop());
        videoCallState.localStream = null;
    }
    
    // Close peer connection
    if (videoCallState.peerConnection) {
        videoCallState.peerConnection.close();
        videoCallState.peerConnection = null;
    }
    
    // Clear video elements
    document.getElementById('local-video').srcObject = null;
    document.getElementById('remote-video').srcObject = null;
    
    // Hide video call interface
    document.getElementById('video-call-container').style.display = 'none';
    document.getElementById('videoCallBtn').style.display = 'block';
    
    // Stop timer
    if (videoCallState.timer) {
        clearInterval(videoCallState.timer);
        videoCallState.timer = null;
    }
    
    // Reset state
    videoCallState.isActive = false;
    videoCallState.isInitiator = false;
    videoCallState.callId = null;
    videoCallState.partnerId = null;
    videoCallState.startTime = null;
    
    // Send end call event if we were in a call
    if (videoCallState.callId) {
        fetch(`/chat/{{ $trade->id }}/video-call/end`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                callId: videoCallState.callId
            })
        }).catch(error => console.warn('Error sending end call:', error));
    }
    
    updateCallStatus('Call ended');
}

// Utility functions
function getPartnerId() {
    // Get the other user in the trade
    const tradeOwnerId = {{ $trade->user_id }};
    const currentUserId = {{ auth()->id() }};
    
    if (currentUserId === tradeOwnerId) {
        // Current user is the trade owner, get the requester
        const acceptedRequest = {!! json_encode($trade->requests()->where('status', 'accepted')->first()) !!};
        return acceptedRequest ? acceptedRequest.requester_id : null;
    } else {
        // Current user is the requester, get the trade owner
        return tradeOwnerId;
    }
}

function generateCallId() {
    return 'call_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

function updateCallStatus(status) {
    document.getElementById('call-status').textContent = status;
    console.log('Call status:', status);
}

function startCallTimer() {
    videoCallState.startTime = Date.now();
    videoCallState.timer = setInterval(() => {
        const elapsed = Date.now() - videoCallState.startTime;
        const minutes = Math.floor(elapsed / 60000);
        const seconds = Math.floor((elapsed % 60000) / 1000);
        document.getElementById('call-timer').textContent = 
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }, 1000);
}

function toggleMute() {
    if (videoCallState.localStream) {
        const audioTrack = videoCallState.localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            videoCallState.isMuted = !audioTrack.enabled;
            const btn = document.getElementById('mute-btn');
            btn.textContent = videoCallState.isMuted ? '🔇' : '🎤';
            btn.style.background = videoCallState.isMuted ? '#dc3545' : '#6c757d';
        }
    }
}

function toggleVideo() {
    if (videoCallState.localStream) {
        const videoTrack = videoCallState.localStream.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            videoCallState.isVideoOff = !videoTrack.enabled;
            const btn = document.getElementById('video-btn');
            btn.textContent = videoCallState.isVideoOff ? '📹' : '📹';
            btn.style.background = videoCallState.isVideoOff ? '#dc3545' : '#6c757d';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Video call system initialized');
});
</script>