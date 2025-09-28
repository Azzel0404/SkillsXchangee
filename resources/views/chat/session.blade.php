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
    
    // Firebase Video Call Integration
    let firebaseVideoCall = null;
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
            console.log('🔥 Initializing Firebase video call integration...');
            
            // Check if Firebase is available globally
            if (typeof firebase === 'undefined') {
                throw new Error('Firebase SDK not loaded. Please include Firebase CDN scripts.');
            }
            
            // Get Firebase config
            const firebaseConfig = window.firebaseConfig || {
                apiKey: "AIzaSyDlx5VjhobiTlqtv69SciHifC7p_xgHELs",
                authDomain: "skillsxchange-c2604.firebaseapp.com",
                databaseURL: "https://skillsxchange-c2604-default-rtdb.asia-southeast1.firebasedatabase.app",
                projectId: "skillsxchange-c2604",
                storageBucket: "skillsxchange-c2604.firebasestorage.app",
                messagingSenderId: "478530945561",
                appId: "1:478530945561:web:646441ceeb5d5c71c02088"
            };
            
            // Initialize Firebase (check if already exists)
            let app;
            try {
                app = firebase.app();
                console.log('Using existing Firebase app');
            } catch (error) {
                app = firebase.initializeApp(firebaseConfig);
                console.log('Created new Firebase app');
            }
            const database = firebase.database();
            
            // Create Firebase video call instance
            firebaseVideoCall = new FirebaseVideoIntegration({
                userId: window.currentUserId,
                tradeId: window.tradeId,
                partnerId: window.partnerId,
                onCallReceived: (call) => {
                    console.log('📞 Incoming call received:', call);
                    showIncomingCallNotification(call);
                },
                onCallAnswered: (stream) => {
                    console.log('📞 Call answered, remote stream received');
                    videoCallState.remoteStream = stream;
                    setupRemoteVideo(stream);
                    updateCallStatus('Connected');
                    videoCallState.isConnected = true;
                },
                onCallEnded: () => {
                    console.log('📞 Call ended');
                    endVideoCall();
                },
                onConnectionStateChange: (state) => {
                    console.log('🔗 Connection state changed:', state);
                    updateCallStatus(state);
                    if (state === 'connected') {
                        videoCallState.isConnected = true;
                    } else if (state === 'failed') {
                        videoCallState.isConnected = false;
                    }
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
                console.log('✅ Firebase video call integration initialized successfully');
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
            console.log('📞 Starting video call...');
            
            if (!firebaseVideoCall) {
                throw new Error('Firebase video call not initialized');
            }
            
            // Update UI
            updateCallStatus('Starting call...');
            document.getElementById('startVideoCallBtn').style.display = 'none';
            document.getElementById('video-call-container').style.display = 'block';
            
            // Start the call
            const success = await firebaseVideoCall.startCall(window.partnerId);
            
            if (success) {
                videoCallState.isActive = true;
                videoCallState.isInitiator = true;
                updateCallStatus('Call initiated, waiting for answer...');
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
            console.log('📞 Answering video call...');
            
            if (!firebaseVideoCall) {
                throw new Error('Firebase video call not initialized');
            }
            
            // Update UI
            updateCallStatus('Answering call...');
            document.getElementById('video-call-container').style.display = 'block';
            document.getElementById('startVideoCallBtn').style.display = 'none';
            
            // Answer the call
            const success = await firebaseVideoCall.answerCall(videoCallState.incomingOffer);
            
            if (success) {
                videoCallState.isActive = true;
                videoCallState.isInitiator = false;
                updateCallStatus('Call answered');
            } else {
                throw new Error('Failed to answer video call');
            }
            
        } catch (error) {
            console.error('❌ Error answering video call:', error);
            showVideoCallError('Failed to answer video call: ' + error.message);
            endVideoCall();
        }
    }
    
    // End video call
    async function endVideoCall() {
        try {
            console.log('📞 Ending video call...');
            
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
            
            // Stop timer
            if (videoCallState.timer) {
                clearInterval(videoCallState.timer);
                videoCallState.timer = null;
            }
            
            // Update UI
            document.getElementById('video-call-container').style.display = 'none';
            document.getElementById('startVideoCallBtn').style.display = 'block';
            updateCallStatus('Call ended');
            
            // Clear video elements
            const localVideo = document.getElementById('local-video');
            const remoteVideo = document.getElementById('remote-video');
            if (localVideo) localVideo.srcObject = null;
            if (remoteVideo) remoteVideo.srcObject = null;
            
        } catch (error) {
            console.error('❌ Error ending video call:', error);
        }
    }
    
    // Toggle mute
    function toggleMute() {
        if (firebaseVideoCall) {
            const isMuted = firebaseVideoCall.toggleMute();
            const muteBtn = document.getElementById('muteBtn');
            if (muteBtn) {
                muteBtn.textContent = isMuted ? 'Unmute' : 'Mute';
                muteBtn.className = isMuted ? 'btn btn-warning' : 'btn btn-success';
            }
        }
    }
    
    // Toggle video
    function toggleVideo() {
        if (firebaseVideoCall) {
            const isVideoOff = firebaseVideoCall.toggleVideo();
            const videoBtn = document.getElementById('videoBtn');
            if (videoBtn) {
                videoBtn.textContent = isVideoOff ? 'Enable Video' : 'Disable Video';
                videoBtn.className = isVideoOff ? 'btn btn-warning' : 'btn btn-success';
            }
        }
    }
    
    // Show incoming call notification
    function showIncomingCallNotification(call) {
        console.log('📞 Showing incoming call notification for:', call);
        
        // Store the incoming offer
        videoCallState.incomingOffer = call.offer;
        videoCallState.partnerId = call.fromUserId;
        videoCallState.callId = call.callId;
        
        // Show notification
        if (window.notificationService) {
            window.notificationService.showIncomingCallNotification(
                call.fromUserName || window.partnerName,
                call.fromUserId,
                window.tradeId
            );
        }
        
        // Show call UI
        document.getElementById('incoming-call-container').style.display = 'block';
        document.getElementById('incoming-call-name').textContent = call.fromUserName || window.partnerName;
    }
    
    // Setup remote video
    function setupRemoteVideo(stream) {
        const remoteVideo = document.getElementById('remote-video');
        if (remoteVideo) {
            remoteVideo.srcObject = stream;
            remoteVideo.style.display = 'block';
        }
    }
    
    // Update call status
    function updateCallStatus(status) {
        const statusElement = document.getElementById('call-status');
        if (statusElement) {
            statusElement.textContent = status;
            
            // Add status-specific styling
            statusElement.className = 'call-status';
            switch (status.toLowerCase()) {
                case 'connected':
                    statusElement.className += ' connected';
                    break;
                case 'failed':
                    statusElement.className += ' failed';
                    break;
                case 'call ended':
                    statusElement.className += ' ended';
                    break;
                default:
                    statusElement.className += ' pending';
            }
        }
        console.log('Call status:', status);
    }
    
    // Show video call error
    function showVideoCallError(message) {
        console.error('📞 Video Call Error:', message);
        
        // Show error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        errorDiv.innerHTML = `
            <div class="flex">
                <div class="py-1">
                    <svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold">Video Call Unavailable</p>
                    <p class="text-sm">${message}</p>
                </div>
            </div>
        `;
        
        // Insert error message
        const container = document.getElementById('video-call-container');
        if (container) {
            container.insertBefore(errorDiv, container.firstChild);
        }
    }
    
    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initializing Firebase video call integration...');
        
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

<!-- Video Call Container -->
<div id="video-call-container" class="video-call-container" style="display: none;">
    <div class="video-call-header">
        <h3>Video Call with {{ $partner->firstname }} {{ $partner->lastname }}</h3>
        <div id="call-status" class="call-status">Ready</div>
        <div id="call-timer" class="call-timer">00:00</div>
    </div>

    <div class="video-call-content">
        <div class="video-grid">
            <div class="video-wrapper">
                <video id="local-video" autoplay muted playsinline></video>
                <div class="video-label">You</div>
            </div>
            <div class="video-wrapper">
                <video id="remote-video" autoplay playsinline></video>
                <div class="video-label">{{ $partner->firstname }}</div>
            </div>
        </div>

        <div class="video-call-controls">
            <button id="muteBtn" class="btn btn-success" onclick="toggleMute()">Mute</button>
            <button id="videoBtn" class="btn btn-success" onclick="toggleVideo()">Disable Video</button>
            <button id="endCallBtn" class="btn btn-danger" onclick="endVideoCall()">End Call</button>
        </div>
    </div>
</div>

<!-- Incoming Call Container -->
<div id="incoming-call-container" class="incoming-call-container" style="display: none;">
    <div class="incoming-call-content">
        <h3>Incoming Call</h3>
        <p id="incoming-call-name">{{ $partner->firstname }} {{ $partner->lastname }}</p>
        <div class="incoming-call-controls">
            <button class="btn btn-success" onclick="answerVideoCall()">Answer</button>
            <button class="btn btn-danger" onclick="endVideoCall()">Decline</button>
        </div>
    </div>
</div>

<!-- Start Video Call Button -->
<button id="startVideoCallBtn" class="btn btn-primary" onclick="startVideoCall()">
    📞 Start Video Call
</button>

<!-- Include Firebase CDN -->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>

<!-- Include Firebase Video Integration -->
<script src="/firebase-video-integration.js"></script>

<!-- Video Call Styles -->
<style>
    .video-call-container {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }

    .video-call-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    .call-status {
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: bold;
    }

    .call-status.connected {
        background: #d4edda;
        color: #155724;
    }

    .call-status.failed {
        background: #f8d7da;
        color: #721c24;
    }

    .call-status.ended {
        background: #fff3cd;
        color: #856404;
    }

    .call-status.pending {
        background: #d1ecf1;
        color: #0c5460;
    }

    .call-timer {
        font-family: monospace;
        font-size: 18px;
        font-weight: bold;
        color: #495057;
    }

    .video-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .video-wrapper {
        position: relative;
        background: #000;
        border-radius: 8px;
        overflow: hidden;
    }

    .video-wrapper video {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .video-label {
        position: absolute;
        bottom: 10px;
        left: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
    }

    .video-call-controls {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .incoming-call-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border: 2px solid #007bff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        text-align: center;
    }

    .incoming-call-content h3 {
        color: #007bff;
        margin-bottom: 10px;
    }

    .incoming-call-controls {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-warning {
        background: #ffc107;
        color: #212529;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn:hover {
        opacity: 0.8;
        transform: translateY(-2px);
    }
</style>

@endsection