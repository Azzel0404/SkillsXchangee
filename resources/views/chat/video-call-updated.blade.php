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
<button id="videoCallBtn" onclick="startVideoCall()"
    style="background: #10b981; color: white; border: none; border-radius: 8px; padding: 10px 20px; cursor: pointer; margin: 10px;">
    <i class="fas fa-video"></i> Start Video Call
</button>

<script>
// Global variables
let localStream = null;
let remoteStream = null;
let peerConnection = null;
let isVideoCallActive = false;
let isMuted = false;
let isVideoEnabled = true;

// Metered API Configuration
const METERED_API_KEY = '511852cda421697270ed9af8b089038b39a7';
const METERED_API_URL = 'https://skillxchange.metered.live/api/v1/turn/credentials';

// Initialize Pusher for video call signaling
const pusher = new Pusher('{{ env("VITE_PUSHER_APP_KEY", "5c02e54d01ca577ae77e") }}', {
    cluster: '{{ env("VITE_PUSHER_APP_CLUSTER", "ap1") }}',
    encrypted: true
});

const channel = pusher.subscribe('trade.' + window.tradeId);

// Fetch TURN server credentials from Metered API
async function fetchTurnCredentials() {
    try {
        console.log('🔄 Fetching TURN server credentials...');
        const response = await fetch(`${METERED_API_URL}?apiKey=${METERED_API_KEY}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const iceServers = await response.json();
        console.log('✅ TURN credentials fetched successfully:', iceServers);
        return iceServers;
    } catch (error) {
        console.error('❌ Error fetching TURN credentials:', error);
        
        // Fallback to basic STUN servers
        return [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
            { urls: 'stun:stun3.l.google.com:19302' },
            { urls: 'stun:stun4.l.google.com:19302' }
        ];
    }
}

// Start video call
async function startVideoCall() {
    try {
        console.log('🎥 Starting video call...');
        updateCallStatus('Initializing...');
        
        // Get user media
        localStream = await navigator.mediaDevices.getUserMedia({
            video: true,
            audio: true
        });
        
        const localVideo = document.getElementById('localVideo');
        localVideo.srcObject = localStream;
        
        // Fetch TURN credentials and create peer connection
        const iceServers = await fetchTurnCredentials();
        await createPeerConnection(iceServers);
        
        // Show video call interface
        document.getElementById('video-call-container').style.display = 'block';
        document.getElementById('videoCallBtn').style.display = 'none';
        
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

// Create peer connection with dynamic TURN server configuration
async function createPeerConnection(iceServers) {
    try {
        console.log('🔗 Creating peer connection with ICE servers:', iceServers);
        
        const configuration = {
            iceServers: iceServers,
            iceCandidatePoolSize: 10,
            bundlePolicy: 'max-bundle',
            rtcpMuxPolicy: 'require',
            iceTransportPolicy: 'all'
        };
        
        peerConnection = new RTCPeerConnection(configuration);
        
        // Add local stream tracks
        if (localStream) {
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });
        }
        
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
        
        console.log('✅ Peer connection created successfully');
        
    } catch (error) {
        console.error('❌ Error creating peer connection:', error);
        throw error;
    }
}

// Handle incoming offer
async function handleVideoCallOffer(data) {
    console.log('📞 Handling video call offer:', data);
    
    try {
        if (!peerConnection) {
            const iceServers = await fetchTurnCredentials();
            await createPeerConnection(iceServers);
        }
        
        await peerConnection.setRemoteDescription(data.offer);
        
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        
        // Send answer via Pusher
        await sendVideoCallAnswer(answer);
        
        updateCallStatus('Answering...');
        
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
    console.log('🧊 Handling ICE candidate:', data);
    
    try {
        if (peerConnection) {
            await peerConnection.addIceCandidate(data.candidate);
        }
    } catch (error) {
        console.error('❌ Error handling ICE candidate:', error);
    }
}

// Send video call offer via Pusher
async function sendVideoCallOffer(offer) {
    try {
        await fetch('/api/video-call/offer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                trade_id: window.tradeId,
                partner_id: window.partnerId,
                offer: offer
            })
        });
    } catch (error) {
        console.error('❌ Error sending offer:', error);
    }
}

// Send video call answer via Pusher
async function sendVideoCallAnswer(answer) {
    try {
        await fetch('/api/video-call/answer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                trade_id: window.tradeId,
                partner_id: window.partnerId,
                answer: answer
            })
        });
    } catch (error) {
        console.error('❌ Error sending answer:', error);
    }
}

// Send ICE candidate via Pusher
async function sendIceCandidate(candidate) {
    try {
        await fetch('/api/video-call/ice-candidate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                trade_id: window.tradeId,
                partner_id: window.partnerId,
                candidate: candidate
            })
        });
    } catch (error) {
        console.error('❌ Error sending ICE candidate:', error);
    }
}

// End video call
function endVideoCall() {
    console.log('📞 Ending video call...');
    
    if (peerConnection) {
        peerConnection.close();
        peerConnection = null;
    }
    
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
    }
    
    if (remoteStream) {
        remoteStream.getTracks().forEach(track => track.stop());
        remoteStream = null;
    }
    
    document.getElementById('video-call-container').style.display = 'none';
    document.getElementById('videoCallBtn').style.display = 'block';
    
    isVideoCallActive = false;
    updateCallStatus('Call ended');
}

// Toggle mute
function toggleMute() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            isMuted = !isMuted;
            document.getElementById('muteBtn').innerHTML = isMuted ? '<i class="fas fa-microphone-slash"></i>' : '<i class="fas fa-microphone"></i>';
        }
    }
}

// Toggle video
function toggleVideo() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            isVideoEnabled = !isVideoEnabled;
            document.getElementById('videoBtn').innerHTML = isVideoEnabled ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
        }
    }
}

// Update call status
function updateCallStatus(status) {
    document.getElementById('callStatus').textContent = status;
    console.log('📞 Call status:', status);
}

// Pusher event listeners
channel.bind('video-call-offer', handleVideoCallOffer);
channel.bind('video-call-answer', handleVideoCallAnswer);
channel.bind('video-call-ice-candidate', handleIceCandidate);
channel.bind('video-call-end', () => {
    console.log('📞 Call ended by partner');
    endVideoCall();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (isVideoCallActive) {
        endVideoCall();
    }
});
</script>

@endsection
