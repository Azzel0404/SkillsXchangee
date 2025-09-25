@extends('layouts.chat')

@section('content')
<script>
    // Initialize global variables for the chat session
    window.currentUserId = parseInt('{{ auth()->id() }}');
    window.tradeId = parseInt('{{ $trade->id }}');
    window.authUserId = parseInt('{{ Auth::id() }}');
    window.partnerId = parseInt('{{ $partner->id }}');
    window.partnerName = '{{ addslashes(($partner->firstname ?? 'Unknown') . ' ' . ($partner->lastname ?? 'User')) }}';
    window.initialMessageCount = parseInt('{{ $messages->count() }}');
</script>

<!-- Video Call Interface -->
<div id="video-call-container"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #000; z-index: 1000;">
    <div style="position: relative; width: 100%; height: 100%;">
        <!-- Remote Video -->
        <video id="remoteVideo" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>

        <!-- Local Video -->
        <video id="localVideo" autoplay playsinline muted
            style="position: absolute; top: 20px; right: 20px; width: 200px; height: 150px; border-radius: 8px; border: 2px solid #fff;"></video>

        <!-- Call Controls -->
        <div
            style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 15px;">
            <button id="muteBtn" onclick="toggleMute()"
                style="background: #374151; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; cursor: pointer;">
                <i class="fas fa-microphone"></i>
            </button>
            <button id="videoBtn" onclick="toggleVideo()"
                style="background: #374151; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; cursor: pointer;">
                <i class="fas fa-video"></i>
            </button>
            <button id="endCallBtn" onclick="endVideoCall()"
                style="background: #ef4444; color: white; border: none; border-radius: 50%; width: 50px; height: 50px; cursor: pointer;">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>

        <!-- Call Status -->
        <div id="callStatus"
            style="position: absolute; top: 20px; left: 20px; background: rgba(0,0,0,0.7); color: white; padding: 10px; border-radius: 5px;">
            Connecting...
        </div>
    </div>
</div>

<!-- Video Call Button -->
<button id="startVideoCallBtn" onclick="startVideoCall()"
    style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 10px;">
    <i class="fas fa-video me-2"></i>Start Video Call
</button>

<script>
    // Video call variables
let localStream = null;
let remoteStream = null;
let peerConnection = null;
let isVideoCallActive = false;
let isMuted = false;
let isVideoOn = true;

// Pusher/Echo configuration
const pusherKey = '{{ env("VITE_PUSHER_APP_KEY", "5c02e54d01ca577ae77e") }}';
const pusherCluster = '{{ env("VITE_PUSHER_APP_CLUSTER", "ap1") }}';

// Initialize Pusher/Echo for video calls
function initializeVideoCallSignaling() {
    console.log('🎥 Initializing video call signaling...');
    
    // Check if Pusher is available
    if (typeof window.Pusher === 'undefined') {
        console.error('❌ Pusher not available');
        return false;
    }
    
    // Check if Echo is available
    if (typeof window.Echo === 'undefined') {
        console.error('❌ Laravel Echo not available');
        return false;
    }
    
    console.log('✅ Pusher and Echo available');
    
    // Create private channel for this trade
    const channelName = `trade.${window.tradeId}`;
    const channel = window.Echo.private(channelName);
    
    console.log(`📡 Listening on channel: ${channelName}`);
    
    // Listen for video call events
    channel.listen('video-call-offer', (data) => {
        console.log('📞 Received video call offer:', data);
        handleVideoCallOffer(data);
    });
    
    channel.listen('video-call-answer', (data) => {
        console.log('📞 Received video call answer:', data);
        handleVideoCallAnswer(data);
    });
    
    channel.listen('video-call-ice-candidate', (data) => {
        console.log('📞 Received ICE candidate:', data);
        handleIceCandidate(data);
    });
    
    channel.listen('video-call-end', (data) => {
        console.log('📞 Video call ended:', data);
        endVideoCall();
    });
    
    return true;
}

// Start video call
async function startVideoCall() {
    console.log('🎥 Starting video call...');
    
    try {
        // Initialize signaling
        if (!initializeVideoCallSignaling()) {
            alert('Video call signaling not available. Please check your connection.');
            return;
        }
        
        // Get user media
        localStream = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480 },
            audio: { echoCancellation: true, noiseSuppression: true }
        });
        
        // Set local video
        const localVideo = document.getElementById('localVideo');
        localVideo.srcObject = localStream;
        
        // Show video call interface
        document.getElementById('video-call-container').style.display = 'block';
        document.getElementById('startVideoCallBtn').style.display = 'none';
        
        // Create peer connection
        await createPeerConnection();
        
        // Add local stream to peer connection
        localStream.getTracks().forEach(track => {
            peerConnection.addTrack(track, localStream);
        });
        
        // Create offer
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        
        // Send offer via Pusher
        await sendVideoCallOffer(offer);
        
        updateCallStatus('Calling...');
        isVideoCallActive = true;
        
    } catch (error) {
        console.error('❌ Error starting video call:', error);
        alert('Failed to start video call: ' + error.message);
    }
}

// Create peer connection with proper configuration
async function createPeerConnection() {
    const configuration = {
        iceServers: [
            // Metered.ca STUN server
            { urls: 'stun:stun.relay.metered.ca:80' },
            // Google STUN servers as backup
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            // Metered.ca TURN servers for reliable connectivity
            {
                urls: 'turn:global.relay.metered.ca:80',
                username: '0582eeabe15281e17e922394',
                credential: 'g7fjNoaIyTpLnkaf'
            },
            {
                urls: 'turn:global.relay.metered.ca:80?transport=tcp',
                username: '0582eeabe15281e17e922394',
                credential: 'g7fjNoaIyTpLnkaf'
            },
            {
                urls: 'turn:global.relay.metered.ca:443',
                username: '0582eeabe15281e17e922394',
                credential: 'g7fjNoaIyTpLnkaf'
            },
            {
                urls: 'turns:global.relay.metered.ca:443?transport=tcp',
                username: '0582eeabe15281e17e922394',
                credential: 'g7fjNoaIyTpLnkaf'
            }
        ],
        iceCandidatePoolSize: 10
    };
    
    peerConnection = new RTCPeerConnection(configuration);
    
    // Handle ICE candidates
    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            console.log('📡 Sending ICE candidate:', event.candidate);
            sendIceCandidate(event.candidate);
        }
    };
    
    // Handle remote stream
    peerConnection.ontrack = (event) => {
        console.log('📹 Received remote stream:', event.streams[0]);
        remoteStream = event.streams[0];
        const remoteVideo = document.getElementById('remoteVideo');
        remoteVideo.srcObject = remoteStream;
        updateCallStatus('Connected');
    };
    
    // Handle connection state changes
    peerConnection.onconnectionstatechange = () => {
        console.log('🔗 Connection state:', peerConnection.connectionState);
        if (peerConnection.connectionState === 'connected') {
            updateCallStatus('Connected');
        } else if (peerConnection.connectionState === 'failed') {
            updateCallStatus('Connection failed');
        }
    };
    
    // Handle ICE connection state changes
    peerConnection.oniceconnectionstatechange = () => {
        console.log('🧊 ICE connection state:', peerConnection.iceConnectionState);
        if (peerConnection.iceConnectionState === 'failed') {
            console.log('🔄 Restarting ICE...');
            peerConnection.restartIce();
        }
    };
}

// Handle incoming offer
async function handleVideoCallOffer(data) {
    console.log('📞 Handling video call offer:', data);
    
    try {
        if (!peerConnection) {
            await createPeerConnection();
        }
        
        await peerConnection.setRemoteDescription(data.offer);
        
        // Create answer
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        
        // Send answer
        await sendVideoCallAnswer(answer);
        
        // Show video call interface
        document.getElementById('video-call-container').style.display = 'block';
        document.getElementById('startVideoCallBtn').style.display = 'none';
        
        updateCallStatus('Answering...');
        isVideoCallActive = true;
        
    } catch (error) {
        console.error('❌ Error handling offer:', error);
    }
}

// Handle incoming answer
async function handleVideoCallAnswer(data) {
    console.log('📞 Handling video call answer:', data);
    
    try {
        await peerConnection.setRemoteDescription(data.answer);
        updateCallStatus('Connected');
    } catch (error) {
        console.error('❌ Error handling answer:', error);
    }
}

// Handle ICE candidate
async function handleIceCandidate(data) {
    console.log('📡 Handling ICE candidate:', data);
    
    try {
        await peerConnection.addIceCandidate(data.candidate);
    } catch (error) {
        console.error('❌ Error handling ICE candidate:', error);
    }
}

// Send offer via Pusher
async function sendVideoCallOffer(offer) {
    try {
        const response = await fetch(`/chat/${window.tradeId}/video-call/offer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                offer: offer,
                callId: `call_${Date.now()}`
            })
        });
        
        const data = await response.json();
        console.log('📤 Offer sent:', data);
    } catch (error) {
        console.error('❌ Error sending offer:', error);
    }
}

// Send answer via Pusher
async function sendVideoCallAnswer(answer) {
    try {
        const response = await fetch(`/chat/${window.tradeId}/video-call/answer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                answer: answer,
                callId: `call_${Date.now()}`
            })
        });
        
        const data = await response.json();
        console.log('📤 Answer sent:', data);
    } catch (error) {
        console.error('❌ Error sending answer:', error);
    }
}

// Send ICE candidate via Pusher
async function sendIceCandidate(candidate) {
    try {
        const response = await fetch(`/chat/${window.tradeId}/video-call/ice-candidate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                candidate: candidate,
                callId: `call_${Date.now()}`
            })
        });
        
        const data = await response.json();
        console.log('📤 ICE candidate sent:', data);
    } catch (error) {
        console.error('❌ Error sending ICE candidate:', error);
    }
}

// End video call
async function endVideoCall() {
    console.log('📞 Ending video call...');
    
    try {
        // Send end call event
        await fetch(`/chat/${window.tradeId}/video-call/end`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        // Close peer connection
        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }
        
        // Stop local stream
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        
        // Hide video call interface
        document.getElementById('video-call-container').style.display = 'none';
        document.getElementById('startVideoCallBtn').style.display = 'inline-block';
        
        isVideoCallActive = false;
        updateCallStatus('Call ended');
        
    } catch (error) {
        console.error('❌ Error ending video call:', error);
    }
}

// Toggle mute
function toggleMute() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            isMuted = !audioTrack.enabled;
            
            const muteBtn = document.getElementById('muteBtn');
            muteBtn.innerHTML = isMuted ? '<i class="fas fa-microphone-slash"></i>' : '<i class="fas fa-microphone"></i>';
        }
    }
}

// Toggle video
function toggleVideo() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            isVideoOn = !videoTrack.enabled;
            
            const videoBtn = document.getElementById('videoBtn');
            videoBtn.innerHTML = isVideoOn ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
        }
    }
}

// Update call status
function updateCallStatus(status) {
    document.getElementById('callStatus').textContent = status;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎥 Video call system initialized');
    
    // Check if Pusher and Echo are available
    if (typeof window.Pusher !== 'undefined' && typeof window.Echo !== 'undefined') {
        console.log('✅ Pusher and Echo available for video calls');
    } else {
        console.error('❌ Pusher or Echo not available');
    }
});
</script>

<!-- Include the rest of your chat interface here -->
<div id="chat-interface">
    <!-- Your existing chat interface -->
</div>
@endsection