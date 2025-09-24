@extends('layouts.chat')

@section('content')
<script>
    // Initialize global variables for the chat session
    // These values are set by the server-side Blade template
    window.currentUserId = parseInt('{{ auth()->id() }}');
    window.tradeId = parseInt('{{ $trade->id }}');
    window.authUserId = parseInt('{{ Auth::id() }}');
    window.partnerId = parseInt('{{ $partner->id }}');
    window.partnerName = '{{ addslashes(($partner->firstname ?? 'Unknown') . ' ' . ($partner->lastname ?? 'User')) }}';
    window.initialMessageCount = parseInt('{{ $messages->count() }}');
</script>
<style>
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

    @keyframes flash {
        0% {
            background-color: rgba(59, 130, 246, 0.3);
        }

        50% {
            background-color: rgba(16, 185, 129, 0.5);
        }

        100% {
            background-color: rgba(59, 130, 246, 0.3);
        }
    }

    .flash-effect {
        animation: flash 0.5s ease-in-out;
    }

    /* Ensure all message bubbles have proper text wrapping */
    #chat-messages>div>div {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }

    #chat-messages>div>div>div:first-child {
        word-break: break-word !important;
        line-height: 1.4 !important;
    }

    /* Video Chat Styles */
    .video-chat-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .video-chat-container {
        background: #1a1a1a;
        border-radius: 12px;
        padding: 0;
        max-width: 95vw;
        width: 95vw;
        max-height: 95vh;
        height: 95vh;
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .video-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        flex: 1;
        padding: 10px;
        min-height: 0;
    }

    .video-grid.maximized {
        grid-template-columns: 1fr;
    }

    .video-item {
        position: relative;
        background: #000;
        border-radius: 8px;
        overflow: hidden;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    .video-item.maximized {
        grid-column: 1 / -1;
        grid-row: 1 / -1;
    }

    .video-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        flex: 1;
    }

    /* Default state for local video - no mirroring by default */
    #local-video {
        transform: scaleX(1);
    }

    .video-item.remote {
        border: 2px solid #3b82f6;
    }

    .video-item.local {
        border: 2px solid #10b981;
    }

    .video-item.local.minimized {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 200px;
        height: 150px;
        z-index: 10;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .video-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        padding: 20px;
        background: rgba(0, 0, 0, 0.8);
        border-top: 1px solid #333;
    }

    .video-btn {
        padding: 12px;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        min-height: 48px;
        font-size: 18px;
    }

    .video-btn.primary {
        background: #3b82f6;
        color: white;
    }

    .video-btn.primary:hover {
        background: #2563eb;
        transform: scale(1.05);
    }

    .video-btn.danger {
        background: #ef4444;
        color: white;
    }

    .video-btn.danger:hover {
        background: #dc2626;
        transform: scale(1.05);
    }

    .video-btn.success {
        background: #10b981;
        color: white;
    }

    .video-btn.success:hover {
        background: #059669;
        transform: scale(1.05);
    }

    .video-btn.secondary {
        background: #6b7280;
        color: white;
    }

    .video-btn.secondary:hover {
        background: #4b5563;
        transform: scale(1.05);
    }

    .video-btn.muted {
        background: #ef4444 !important;
    }

    .video-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }

    .video-btn.maximize {
        background: #8b5cf6;
        color: white;
    }

    .video-btn.maximize:hover {
        background: #7c3aed;
        transform: scale(1.05);
    }

    .video-status {
        text-align: center;
        padding: 15px;
        font-weight: 600;
        color: #e5e7eb;
        background: rgba(0, 0, 0, 0.5);
        border-bottom: 1px solid #333;
    }

    .call-timer {
        text-align: center;
        font-size: 1.1rem;
        font-weight: 600;
        color: #3b82f6;
        padding: 10px;
        background: rgba(0, 0, 0, 0.3);
        border-bottom: 1px solid #333;
    }

    .close-video {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        font-size: 1.2rem;
        z-index: 1000;
        transition: all 0.2s;
    }

    .close-video:hover {
        background: rgba(239, 68, 68, 0.8);
        transform: scale(1.1);
    }

    .connection-status {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        backdrop-filter: blur(10px);
        z-index: 10;
    }

    .connection-status.connected {
        background: rgba(16, 185, 129, 0.9);
        color: white;
    }

    .connection-status.connecting {
        background: rgba(245, 158, 11, 0.9);
        color: white;
    }

    .connection-status.disconnected {
        background: rgba(239, 68, 68, 0.9);
        color: white;
    }

    .video-overlay {
        position: absolute;
        bottom: 10px;
        left: 10px;
        right: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
    }

    .user-name {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }

    .video-controls-overlay {
        display: flex;
        gap: 8px;
    }

    .control-btn {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s;
        backdrop-filter: blur(10px);
    }

    .control-btn:hover {
        background: rgba(0, 0, 0, 0.9);
        transform: scale(1.1);
    }

    .control-btn.active {
        background: rgba(239, 68, 68, 0.9);
    }

    /* Emoji button hover effect */
    #emoji-button:hover {
        background-color: #f3f4f6 !important;
    }

    #emoji-button:active {
        background-color: #e5e7eb !important;
    }

    /* Message styles */
    .message-container {
        margin-bottom: 16px;
        display: flex;
    }

    .message-container[data-sender="{{ Auth::id() }}"] {
        justify-content: flex-end;
    }

    .message-container:not([data-sender="{{ Auth::id() }}"]) {
        justify-content: flex-start;
    }

    .message-bubble {
        max-width: 70%;
        padding: 12px;
        border-radius: 12px;
        position: relative;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .message-bubble[data-sender="{{ Auth::id() }}"] {
        background: #3b82f6;
        color: white;
    }

    .message-bubble:not([data-sender="{{ Auth::id() }}"]) {
        background: #e5e7eb;
        color: #374151;
    }

    .message-content {
        margin-bottom: 4px;
        word-break: break-word;
        line-height: 1.4;
    }

    .message-time {
        font-size: 0.75rem;
        opacity: 0.8;
    }
</style>

<!-- Video Chat Modal -->
<div id="video-chat-modal" class="video-chat-modal">
    <div class="video-chat-container">
        <button class="close-video" onclick="closeVideoChat()">×</button>

        <div class="video-status" id="video-status">Initializing video chat...</div>
        <div class="call-timer" id="call-timer" style="display: none;">00:00</div>

        <div class="video-grid" id="video-grid">
            <div class="video-item local" id="local-video-item">
                <video id="local-video" autoplay muted playsinline></video>
                <div class="connection-status" id="local-status">Local</div>
                <div class="video-overlay">
                    <div class="user-name">{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</div>
                    <div class="video-controls-overlay">
                        <button class="control-btn" id="local-maximize-btn" onclick="maximizeVideo('local')"
                            title="Maximize">⛶</button>
                    </div>
                </div>
            </div>
            <div class="video-item remote" id="remote-video-item">
                <video id="remote-video" autoplay playsinline></video>
                <div class="connection-status" id="remote-status">Waiting...</div>
                <div class="video-overlay">
                    <div class="user-name" id="remote-user-name">{{ $partner->firstname ?? 'Partner' }} {{
                        $partner->lastname ?? '' }}</div>
                    <div class="video-controls-overlay">
                        <button class="control-btn" id="remote-maximize-btn" onclick="maximizeVideo('remote')"
                            title="Maximize">⛶</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="video-controls">
            <button id="auto-call-toggle" class="video-btn secondary" onclick="toggleAutoCall()"
                title="Toggle Auto-call" style="background: #10b981;">🔗 Auto-call ON</button>
            <div id="presence-status"
                style="color: #6b7280; font-size: 0.875rem; margin: 0 8px; display: flex; align-items: center;">🔴
                Partner is offline</div>
            <button id="start-call-btn" class="video-btn primary" onclick="startVideoCall()"
                title="Start Call">📞</button>
            <button id="end-call-btn" class="video-btn danger" onclick="endVideoCall()" style="display: none;"
                title="End Call">📞</button>
            <button id="toggle-audio-btn" class="video-btn success" onclick="toggleAudio()" style="display: none;"
                title="Mute/Unmute">🎤</button>
            <button id="toggle-video-btn" class="video-btn success" onclick="toggleVideo()" style="display: none;"
                title="Turn Video On/Off">📹</button>
            <button id="mirror-video-btn" class="video-btn secondary" onclick="toggleMirror()" style="display: none;"
                title="Mirror Video">🪞</button>
            <button id="screen-share-btn" class="video-btn secondary" onclick="toggleScreenShare()"
                style="display: none;" title="Share Screen">🖥️</button>
            <button id="maximize-btn" class="video-btn maximize" onclick="toggleMaximize()" style="display: none;"
                title="Maximize">⛶</button>
            <button id="chat-toggle-btn" class="video-btn secondary" onclick="toggleChat()" style="display: none;"
                title="Toggle Chat">💬</button>
        </div>
    </div>
</div>

<div style="height: 100vh; display: flex; flex-direction: column;">
    <!-- Header -->
    <div
        style="background: #1e40af; color: white; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 1.5rem; font-weight: bold;">SkillsXchange</div>
        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            style="color: #ef4444; text-decoration: none;">Logout</a>
    </div>

    <!-- Active Trade Session Banner -->
    <div style="background: #1e40af; color: white; padding: 12px 16px; text-align: center;">
        <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 4px;">
            💛 Active Trade Session
        </div>
        <div style="font-size: 0.9rem;">
            Trading: {{ $trade->offeringSkill->name ?? 'Unknown' }} for {{ $trade->lookingSkill->name ?? 'Unknown' }}
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 1; display: flex; overflow: hidden;">
        <!-- Session Chat (Left Panel) -->
        <div style="flex: 1; display: flex; flex-direction: column; border-right: 1px solid #e5e7eb;">
            <!-- Chat Header -->
            <div
                style="background: #1e40af; color: white; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>💬</span>
                    <span>Session Chat</span>
                    <span id="new-message-indicator"
                        style="display: none; background: #ef4444; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem; animation: pulse 2s infinite;">NEW</span>
                    <div id="connection-status" style="margin-left: 12px; font-size: 0.7rem;">
                        <span id="status-indicator"
                            style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#10b981; margin-right:4px;"></span>
                        <span id="status-text">Connecting...</span>
                    </div>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button id="video-call-btn"
                        style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;"
                        onclick="openVideoChat()">📷</button>
                    <button
                        style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">🎤</button>
                    <button
                        style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">⚠️</button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages" style="flex: 1; padding: 16px; overflow-y: auto; background: #f9fafb;">
                @foreach($messages as $message)
                <div class="message-container" data-sender="{{ $message->sender_id }}" data-auth="{{ Auth::id() }}">
                    <div class="message-bubble" data-sender="{{ $message->sender_id }}" data-auth="{{ Auth::id() }}">
                        <div class="message-content">{{ $message->message }}</div>
                        <div class="message-time">{{ $message->created_at->format('g:i A') }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Message Input -->
            <div style="padding: 16px; background: white; border-top: 1px solid #e5e7eb;">
                <form id="message-form" style="display: flex; gap: 8px;">
                    <div style="flex: 1; position: relative;">
                        <input type="text" id="message-input" placeholder="Type your message here..."
                            style="width: 100%; padding: 12px 40px 12px 12px; border: 1px solid #d1d5db; border-radius: 6px; outline: none;">
                        <button type="button" id="emoji-button"
                            style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 18px; cursor: pointer; padding: 4px; border-radius: 4px; transition: background-color 0.2s;"
                            title="Add emoji">😊</button>
                    </div>
                    <div style="display: flex; gap: 4px; align-items: center;">
                        <input type="file" id="image-upload" accept="image/*" style="display: none;"
                            onchange="handleImageUpload(event)">
                        <input type="file" id="video-upload" accept="video/*" style="display: none;"
                            onchange="handleVideoUpload(event)">
                        <button type="button" onclick="document.getElementById('image-upload').click()"
                            style="padding: 8px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer; font-size: 16px;"
                            title="Send Image">📷</button>
                        <button type="button" onclick="document.getElementById('video-upload').click()"
                            style="padding: 8px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer; font-size: 16px;"
                            title="Send Video">🎥</button>
                        <button type="submit" id="send-button"
                            style="background: #1e40af; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Send</button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Session Tasks (Right Sidebar) -->
        <div
            style="width: 350px; background: white; border-left: 1px solid #e5e7eb; display: flex; flex-direction: column;">
            <!-- Sidebar Header -->
            <div
                style="background: #f3f4f6; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 8px;">
                <span>☑️</span>
                <span style="font-weight: 600;">Session Tasks</span>
            </div>

            <!-- Tasks Content -->
            <div style="flex: 1; padding: 16px; overflow-y: auto;">
                <!-- Your Tasks -->
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 12px; color: #374151;">Your Tasks</h3>
                    <div id="my-tasks">
                        @forelse($myTasks as $task)
                        <div class="task-item" data-task-id="{{ $task->id }}"
                            style="margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <input type="checkbox" {{ $task->completed ? 'checked' : '' }}
                                onchange="toggleTask({{ $task->id }})"
                                style="width: 16px; height: 16px;">
                                <span
                                    style="font-weight: 500; {{ $task->completed ? 'text-decoration: line-through; color: #6b7280;' : '' }}">{{
                                    $task->title }}</span>
                            </div>
                            @if($task->description)
                            <div style="font-size: 0.875rem; color: #6b7280; margin-left: 24px;">{{ $task->description
                                }}</div>
                            @endif
                        </div>
                        @empty
                        <div style="color: #6b7280; font-size: 0.875rem; text-align: center; padding: 16px;">No tasks
                            assigned to you</div>
                        @endforelse
                    </div>

                    <!-- Your Progress -->
                    <div style="margin-top: 16px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.875rem; color: #6b7280;">Progress</span>
                            <span id="my-progress-text" style="font-size: 0.875rem; font-weight: 600;"
                                data-progress="{{ round($myProgress) }}">{{ round($myProgress) }}%</span>
                        </div>
                        <div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div id="my-progress-bar"
                                style="background: #10b981; height: 100%; transition: width 0.3s ease;"
                                data-progress="{{ $myProgress }}"></div>
                        </div>
                    </div>
                </div>

                <!-- Partner's Tasks -->
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 12px; color: #374151;">{{
                        $partner->firstname }}'s Tasks</h3>
                    <div id="partner-tasks">
                        @forelse($partnerTasks as $task)
                        <div class="task-item" data-task-id="{{ $task->id }}"
                            style="margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <input type="checkbox" {{ $task->completed ? 'checked' : '' }} disabled style="width:
                                16px; height: 16px;">
                                <span
                                    style="font-weight: 500; {{ $task->completed ? 'text-decoration: line-through; color: #6b7280;' : '' }}">{{
                                    $task->title }}</span>

                                <!-- Verification Status Badge -->
                                @if($task->completed)
                                @if($task->verified)
                                <span
                                    style="background: #10b981; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">✓
                                    Verified</span>
                                @else
                                <span
                                    style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">⏳
                                    Pending Verification</span>
                                @endif
                                @endif
                            </div>
                            @if($task->description)
                            <div style="font-size: 0.875rem; color: #6b7280; margin-left: 24px;">{{ $task->description
                                }}</div>
                            @endif

                            <!-- Verification Notes -->
                            @if($task->verified && $task->verification_notes)
                            <div
                                style="font-size: 0.875rem; color: #059669; margin-left: 24px; margin-top: 4px; font-style: italic;">
                                <strong>Verification:</strong> {{ $task->verification_notes }}
                            </div>
                            @endif

                            <!-- Verification Actions -->
                            @if($task->completed && !$task->verified && $task->created_by == auth()->id())
                            <div style="margin-top: 8px; margin-left: 24px;">
                                <button class="verify-btn" data-task-id="{{ $task->id }}"
                                    data-task-title="{{ $task->title }}" data-verify="true"
                                    style="background: #10b981; color: white; padding: 4px 12px; border: none; border-radius: 4px; font-size: 0.75rem; cursor: pointer; margin-right: 8px;">
                                    ✓ Verify
                                </button>
                                <button class="verify-btn" data-task-id="{{ $task->id }}"
                                    data-task-title="{{ $task->title }}" data-verify="false"
                                    style="background: #ef4444; color: white; padding: 4px 12px; border: none; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">
                                    ✗ Reject
                                </button>
                            </div>
                            @endif
                        </div>
                        @empty
                        <div style="color: #6b7280; font-size: 0.875rem; text-align: center; padding: 16px;">No tasks
                            assigned to {{ $partner->firstname }}</div>
                        @endforelse
                    </div>

                    <!-- Partner's Progress -->
                    <div style="margin-top: 16px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.875rem; color: #6b7280;">Progress</span>
                            <span id="partner-progress-text" style="font-size: 0.875rem; font-weight: 600;"
                                data-progress="{{ round($partnerProgress) }}">{{ round($partnerProgress) }}%</span>
                        </div>
                        <div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div id="partner-progress-bar"
                                style="background: #3b82f6; height: 100%; transition: width 0.3s ease;"
                                data-progress="{{ $partnerProgress }}"></div>
                        </div>
                    </div>
                </div>

                <!-- Skill Learning Status -->
                <div
                    style="margin-bottom: 24px; padding: 16px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 12px; color: #0c4a6e;">🎓 Skill
                        Learning Progress</h3>
                    <div id="skill-learning-status">
                        <div style="text-align: center; color: #6b7280; font-size: 0.875rem;">
                            Loading skill learning status...
                        </div>
                    </div>
                    <div id="complete-session-section" style="margin-top: 16px; display: none;">
                        <button onclick="completeSession()" id="complete-session-btn"
                            style="width: 100%; background: #10b981; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            ✅ Complete Session & Learn Skills
                        </button>
                    </div>
                </div>

                <!-- Add Task Button -->
                <button onclick="showAddTaskModal()"
                    style="width: 100%; background: #1e40af; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    + Add Task
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div
        style="background: #f3f4f6; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e5e7eb;">
        <div style="font-size: 0.875rem; color: #6b7280;">
            <div>Session started: {{ \Carbon\Carbon::parse($trade->start_date)->format('M d, Y') }} at {{
                \Carbon\Carbon::parse($trade->start_date)->format('g:i A') }}</div>
            <div>Current time: <span id="current-time">{{ now()->format('g:i A') }}</span> • Duration: <span
                    id="session-duration">0 minutes</span></div>
            <div>Status: <span id="session-status" style="color: #10b981; font-weight: 600;">🟢 Active</span> • Tasks:
                <span id="task-count">0</span>
            </div>
        </div>
        <button onclick="endSession()"
            style="background: #ef4444; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">End
            Session</button>
    </div>
</div>

<!-- Add Task Modal -->
<div id="add-task-modal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;"
    onclick="handleModalClick(event)">
    <div style="background: white; padding: 24px; border-radius: 8px; width: 400px; max-width: 90%;"
        onclick="event.stopPropagation()">
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 16px;">Add Task</h3>
        <div style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 4px; font-weight: 500;">Assign Task To</label>
            <select id="task-assignee" required
                style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                <option value="{{ $partner->id }}">{{ $partner->firstname }} {{ $partner->lastname }}</option>
                <option value="{{ Auth::id() }}">Myself</option>
            </select>
        </div>
        <form id="add-task-form">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 4px; font-weight: 500;">Task Title</label>
                <input type="text" id="task-title" required
                    style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 4px; font-weight: 500;">Description (Optional)</label>
                <textarea id="task-description" rows="3"
                    style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; resize: vertical;"></textarea>
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" onclick="hideAddTaskModal()"
                    style="padding: 8px 16px; border: 1px solid #d1d5db; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit"
                    style="padding: 8px 16px; background: #1e40af; color: white; border: none; border-radius: 4px; cursor: pointer;">Add
                    Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Task Verification Modal -->
<div id="verification-modal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;"
    onclick="handleVerificationModalClick(event)">
    <div style="background: white; border-radius: 8px; padding: 24px; width: 90%; max-width: 500px; max-height: 80vh; overflow-y: auto;"
        onclick="event.stopPropagation()">
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 16px; color: #374151;"
            id="verification-modal-title">Verify Task</h3>
        <form id="verification-form">
            <input type="hidden" id="verification-task-id" name="task_id">
            <input type="hidden" id="verification-verified" name="verified">

            <div style="margin-bottom: 16px;">
                <label
                    style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 8px;">Verification
                    Notes (Optional)</label>
                <textarea id="verification-notes" name="verification_notes"
                    style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 0.875rem; resize: vertical; min-height: 80px;"
                    placeholder="Add any feedback or notes about the task completion..."></textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="hideVerificationModal()"
                    style="padding: 8px 16px; border: 1px solid #d1d5db; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" id="verification-submit-btn"
                    style="padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: 500;">Verify
                    Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden logout form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<script>
    // Laravel Echo is already initialized in bootstrap.js
// We'll use it to listen for events

        // Debug information
        console.log('=== CHAT DEBUG INFO ===');
        console.log('Trade ID:', window.tradeId);
        console.log('User ID:', window.authUserId);
        console.log('Laravel Echo available:', !!window.Echo);
        console.log('Pusher available:', !!window.Pusher);
        console.log('Current URL:', window.location.href);
        console.log('Base URL:', window.location.origin);
        console.log('Generated message URL:', window.location.origin + '/chat/{{ $trade->id }}/message');

// Listen for events using Laravel Echo
if (window.Echo) {
    console.log('Initializing Pusher connection for trade {{ $trade->id }}');
    
    // Connection status monitoring
    window.Echo.connector.pusher.connection.bind('connected', function() {
        console.log('✅ Pusher connected successfully');
        updateConnectionStatus('connected');
    });
    
    window.Echo.connector.pusher.connection.bind('disconnected', function() {
        console.log('❌ Pusher disconnected');
        updateConnectionStatus('disconnected');
    });
    
    window.Echo.connector.pusher.connection.bind('error', function(error) {
        console.error('🚨 Pusher connection error:', error);
        updateConnectionStatus('error');
    });
    
    // Additional debugging
    window.Echo.connector.pusher.connection.bind('connecting', function() {
        console.log('🔄 Pusher connecting...');
        updateConnectionStatus('connecting');
    });

    // Listen for new messages
    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('new-message', function(data) {
            console.log('Received new message event:', data);
            // Only add if it's not from the current user (to avoid duplicates)
            if (data.message.sender_id !== window.authUserId) {
                addMessageToChat(data.message, data.sender_name, data.timestamp, false);
            } else {
                // For our own messages, just update the timestamp if needed
                const existingMessage = document.querySelector(`[data-confirmed="true"]`);
                if (existingMessage) {
                    const timestampElement = existingMessage.querySelector('div[style*="font-size: 0.75rem"]');
                    if (timestampElement) {
                        timestampElement.textContent = data.timestamp;
                    }
                }
            }
        });

    // Listen for task updates
    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('task-updated', function(data) {
            console.log('Received task update event:', data);
            updateTask(data.task);
            updateProgress();
        });

    // Listen for video call events
    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('video-call-offer', async function(data) {
            console.log('Received video call offer:', data);
            if (data.fromUserId !== window.authUserId) {
                await handleVideoCallOffer(data);
            }
        });

    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('video-call-answer', async function(data) {
            console.log('Received video call answer:', data);
            if (data.toUserId === window.authUserId) {
                await handleVideoCallAnswer(data);
            }
        });

    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('video-call-ice-candidate', async function(data) {
            console.log('Received ICE candidate:', data);
            if (data.toUserId === window.authUserId) {
                await handleIceCandidate(data);
            }
        });

    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('video-call-end', function(data) {
            console.log('Received video call end:', data);
            if (data.fromUserId !== window.authUserId) {
                handleVideoCallEnd(data);
            }
        });

    // Listen for user presence events
    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('user-joined', function(data) {
            console.log('User joined:', data);
            handleUserJoined(data);
        });

    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('user-left', function(data) {
            console.log('User left:', data);
            handleUserLeft(data);
        });
} else {
    console.error('Laravel Echo not available. Make sure Pusher is properly configured.');
    updateConnectionStatus('error');
}

// Connection status update function
function updateConnectionStatus(status) {
    const indicator = document.getElementById('status-indicator');
    const text = document.getElementById('status-text');
    
    if (!indicator || !text) return;
    
    switch(status) {
        case 'connected':
            indicator.style.background = '#10b981';
            text.textContent = 'Connected';
            break;
        case 'disconnected':
            indicator.style.background = '#f59e0b';
            text.textContent = 'Disconnected';
            break;
        case 'error':
            indicator.style.background = '#ef4444';
            text.textContent = 'Connection Error';
            break;
        default:
            indicator.style.background = '#6b7280';
            text.textContent = 'Connecting...';
    }
}

// Message handling with debounce
let isSending = false;
document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (message && !isSending) {
        isSending = true;
        sendMessage(message);
        input.value = '';
        
        // Prevent rapid sending (reduced from 1000ms to 300ms for better responsiveness)
        setTimeout(() => {
            isSending = false;
        }, 300);
    }
});

function sendMessage(message) {
    console.log('📤 Sending message:', message);
    
    // Show loading state
    const sendButton = document.getElementById('send-button');
    const originalText = sendButton.textContent;
    sendButton.textContent = 'Sending...';
    sendButton.disabled = true;
    sendButton.style.background = '#6b7280';
    
    // Add message to UI immediately (optimistic update)
    const tempId = 'temp_' + Date.now();
    addMessageToChat(message, '{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}', new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}), true, tempId);
    
    // Generate absolute URL for production compatibility
    const baseUrl = window.location.origin;
    const url = baseUrl + '/chat/{{ $trade->id }}/message';
    console.log('📡 Sending to URL:', url);
    console.log('📡 CSRF Token:', '{{ csrf_token() }}');
    console.log('📡 Base URL:', baseUrl);
    
    // Check if URL is valid
    if (!url || url.includes('undefined') || !url.includes('/chat/')) {
        console.error('❌ Invalid URL generated:', url);
        showError('Invalid chat URL. Please refresh the page.');
        return;
    }
    
    // Add credentials for CORS
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ message: message }),
        credentials: 'same-origin' // Important for CORS
    })
    .then(response => {
        console.log('📨 Response status:', response.status);
        console.log('📨 Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('📨 Response data:', data);
        
        // Reset button state
        sendButton.textContent = originalText;
        sendButton.disabled = false;
        sendButton.style.background = '#1e40af';
        
        if (data.success) {
            console.log('✅ Message sent successfully');
            // Update the temporary message with the real one and mark it as confirmed
            updateMessageInChat(tempId, data.message);
            // Mark this message as confirmed to prevent duplicate Echo events
            const messageElement = document.querySelector(`[data-temp-id="${tempId}"]`);
            if (messageElement) {
                messageElement.setAttribute('data-confirmed', 'true');
                messageElement.removeAttribute('data-temp-id');
            }
        } else {
            console.error('❌ Message send failed:', data.error);
            // Remove the temporary message if it failed
            removeMessageFromChat(tempId);
            showError('Failed to send message: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('🚨 Fetch error:', error);
        console.error('🚨 Error type:', error.name);
        console.error('🚨 Error message:', error.message);
        
        // Reset button state
        sendButton.textContent = originalText;
        sendButton.disabled = false;
        sendButton.style.background = '#1e40af';
        
        // Remove the temporary message if it failed
        removeMessageFromChat(tempId);
        
        // Show specific error messages based on error type
        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            showError('Network error: Unable to connect to server. Please check your internet connection and try again.');
        } else if (error.name === 'TypeError' && error.message.includes('NetworkError')) {
            showError('Network error: Please check your internet connection.');
        } else if (error.message.includes('CORS')) {
            showError('CORS error: Cross-origin request blocked. Please refresh the page.');
        } else if (error.message.includes('HTTP error')) {
            showError('Server error: ' + error.message);
        } else {
            showError('Failed to send message: ' + error.message);
        }
    });
}

function addMessageToChat(message, senderName, timestamp, isOwn, tempId = null) {
    // Check for duplicate messages to prevent double display
    if (isOwn) {
        const messageText = typeof message === 'string' ? message : message.message;
        const existingMessages = document.querySelectorAll('#chat-messages > div');
        const lastMessage = existingMessages[existingMessages.length - 1];
        
        if (lastMessage && lastMessage.querySelector('div[style*="background: #3b82f6"]')) {
            const lastMessageText = lastMessage.querySelector('div[style*="margin-bottom: 4px"]').textContent;
            if (lastMessageText === messageText) {
                console.log('Duplicate message detected, skipping...');
                return lastMessage;
            }
        }
    }
    
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.style.marginBottom = '16px';
    messageDiv.style.display = 'flex';
    messageDiv.style.justifyContent = isOwn ? 'flex-end' : 'flex-start';
    
    if (tempId) {
        messageDiv.setAttribute('data-temp-id', tempId);
    }
    
    // Handle both string messages and message objects
    const messageText = typeof message === 'string' ? message : message.message;
    const messageTime = timestamp || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    // Check if message contains image or video
    let messageContent = '';
    if (messageText.includes('[IMAGE:') && messageText.includes(']')) {
        const fileName = messageText.match(/\[IMAGE:(.+?)\]/)[1];
        messageContent = `
            <div style="max-width: 70%; ${isOwn ? 'background: #3b82f6; color: white;' : 'background: #e5e7eb; color: #374151;'} padding: 12px; border-radius: 12px; position: relative; word-wrap: break-word; overflow-wrap: break-word;">
                <div style="margin-bottom: 8px;">
                    <img src="${window.tempImageData || '#'}" alt="${fileName}" style="max-width: 200px; max-height: 200px; border-radius: 8px; object-fit: cover;" onerror="this.style.display='none'">
                </div>
                <div style="font-size: 0.75rem; opacity: 0.8;">${fileName}</div>
                <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 4px;">${messageTime}</div>
            </div>
        `;
    } else if (messageText.includes('[VIDEO:') && messageText.includes(']')) {
        const fileName = messageText.match(/\[VIDEO:(.+?)\]/)[1];
        messageContent = `
            <div style="max-width: 70%; ${isOwn ? 'background: #3b82f6; color: white;' : 'background: #e5e7eb; color: #374151;'} padding: 12px; border-radius: 12px; position: relative; word-wrap: break-word; overflow-wrap: break-word;">
                <div style="margin-bottom: 8px;">
                    <video controls style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        <source src="${window.tempVideoData || '#'}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div style="font-size: 0.75rem; opacity: 0.8;">${fileName}</div>
                <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 4px;">${messageTime}</div>
            </div>
        `;
    } else {
        messageContent = `
            <div style="max-width: 70%; ${isOwn ? 'background: #3b82f6; color: white;' : 'background: #e5e7eb; color: #374151;'} padding: 12px; border-radius: 12px; position: relative; word-wrap: break-word; overflow-wrap: break-word;">
                <div style="margin-bottom: 4px; word-break: break-word; line-height: 1.4;">${messageText}</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">${messageTime}</div>
            </div>
        `;
    }
    
    messageDiv.innerHTML = messageContent;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Flash effect for new messages (only for incoming messages, not your own)
    if (!isOwn) {
        console.log('🆕 New message added dynamically:', messageText);
        flashChatArea();
    }
    
    return messageDiv;
}

// Add flash effect function
function flashChatArea() {
    const chatMessages = document.getElementById('chat-messages');
    
    // Create flash overlay
    const flashOverlay = document.createElement('div');
    flashOverlay.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(59, 130, 246, 0.3), rgba(16, 185, 129, 0.3));
        border-radius: 8px;
        pointer-events: none;
        z-index: 10;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    // Position the overlay relative to chat messages
    chatMessages.style.position = 'relative';
    chatMessages.appendChild(flashOverlay);
    
    // Trigger flash animation
    setTimeout(() => {
        flashOverlay.style.opacity = '1';
    }, 50);
    
    setTimeout(() => {
        flashOverlay.style.opacity = '0';
    }, 150);
    
    // Remove overlay after animation
    setTimeout(() => {
        if (flashOverlay.parentNode) {
            flashOverlay.parentNode.removeChild(flashOverlay);
        }
    }, 500);
    
    // Show new message indicator
    showNewMessageIndicator();
}

// Show new message indicator
function showNewMessageIndicator() {
    const indicator = document.getElementById('new-message-indicator');
    if (indicator) {
        indicator.style.display = 'inline-block';
        
        // Hide after 3 seconds
        setTimeout(() => {
            indicator.style.display = 'none';
        }, 3000);
    }
}

// Show error message function
function showError(message) {
    console.error('Error:', message);
    
    // Create error notification
    const errorDiv = document.createElement('div');
    errorDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ef4444;
        color: white;
        padding: 12px 16px;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-width: 300px;
        word-wrap: break-word;
    `;
    errorDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: auto;">×</button>
        </div>
    `;
    
    document.body.appendChild(errorDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.remove();
        }
    }, 5000);
}

// Show success message function
function showSuccess(message) {
    console.log('Success:', message);
    
    // Create success notification
    const successDiv = document.createElement('div');
    successDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 12px 16px;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-width: 300px;
        word-wrap: break-word;
    `;
    
    successDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px;">
            <span>✅</span>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: auto;">×</button>
        </div>
    `;
    
    document.body.appendChild(successDiv);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.remove();
        }
    }, 3000);
}

// Remove message from chat function
function removeMessageFromChat(tempId) {
    const messageElement = document.querySelector(`[data-temp-id="${tempId}"]`);
    if (messageElement) {
        messageElement.remove();
    }
}

function updateMessageInChat(tempId, messageData) {
    const messageDiv = document.querySelector(`[data-temp-id="${tempId}"]`);
    if (messageDiv) {
        // Update with real message data
        messageDiv.removeAttribute('data-temp-id');
        messageDiv.setAttribute('data-message-id', messageData.id);
        
        const messageText = messageData.message;
        const messageTime = new Date(messageData.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        messageDiv.innerHTML = `
            <div style="max-width: 70%; background: #3b82f6; color: white; padding: 12px; border-radius: 12px; position: relative; word-wrap: break-word; overflow-wrap: break-word;">
                <div style="margin-bottom: 4px; word-break: break-word; line-height: 1.4;">${messageText}</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">${messageTime}</div>
            </div>
        `;
    }
}

function removeMessageFromChat(tempId) {
    const messageDiv = document.querySelector(`[data-temp-id="${tempId}"]`);
    if (messageDiv) {
        messageDiv.remove();
    }
}

// Task handling
function toggleTask(taskId) {
    fetch(`/chat/task/${taskId}/toggle`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateTask(data.task);
            updateProgress();
            // Refresh skill learning status
            loadSkillLearningStatus();
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateTask(task) {
    const taskElement = document.querySelector(`[data-task-id="${task.id}"]`);
    if (taskElement) {
        const checkbox = taskElement.querySelector('input[type="checkbox"]');
        const title = taskElement.querySelector('span');
        
        checkbox.checked = task.completed;
        if (task.completed) {
            title.style.textDecoration = 'line-through';
            title.style.color = '#6b7280';
        } else {
            title.style.textDecoration = 'none';
            title.style.color = '';
        }
    }
}

function updateProgress() {
    // Recalculate progress without reloading
    const myTasks = document.querySelectorAll('#my-tasks .task-item');
    const myCompletedTasks = document.querySelectorAll('#my-tasks .task-item input[type="checkbox"]:checked');
    const myProgress = myTasks.length > 0 ? (myCompletedTasks.length / myTasks.length) * 100 : 0;
    
    const partnerTasks = document.querySelectorAll('#partner-tasks .task-item');
    const partnerCompletedTasks = document.querySelectorAll('#partner-tasks .task-item input[type="checkbox"]:checked');
    const partnerProgress = partnerTasks.length > 0 ? (partnerCompletedTasks.length / partnerTasks.length) * 100 : 0;
    
    // Update progress bars
    const myProgressBar = document.querySelector('#my-tasks + div div[style*="background: #10b981"]');
    const partnerProgressBar = document.querySelector('#partner-tasks + div div[style*="background: #3b82f6"]');
    
    if (myProgressBar) {
        myProgressBar.style.width = myProgress + '%';
        myProgressBar.parentElement.previousElementSibling.querySelector('span:last-child').textContent = Math.round(myProgress) + '%';
    }
    
    if (partnerProgressBar) {
        partnerProgressBar.style.width = partnerProgress + '%';
        partnerProgressBar.parentElement.previousElementSibling.querySelector('span:last-child').textContent = Math.round(partnerProgress) + '%';
    }
    
    // Update task count in session info
    updateTaskCount();
}

function updateTaskCount() {
    const myTasks = document.querySelectorAll('#my-tasks .task-item').length;
    const partnerTasks = document.querySelectorAll('#partner-tasks .task-item').length;
    const totalTasks = myTasks + partnerTasks;
    
    const taskCountElement = document.getElementById('task-count');
    if (taskCountElement) {
        taskCountElement.textContent = totalTasks;
        
        // Update color based on task count
        if (totalTasks === 0) {
            taskCountElement.style.color = '#ef4444'; // Red for no tasks
        } else if (totalTasks < 3) {
            taskCountElement.style.color = '#f59e0b'; // Orange for few tasks
        } else {
            taskCountElement.style.color = '#10b981'; // Green for good task count
        }
    }
}

// Modal handling
function showAddTaskModal() {
    const modal = document.getElementById('add-task-modal');
    modal.style.display = 'flex';
    // Clear form when opening
    document.getElementById('add-task-form').reset();
}

function hideAddTaskModal() {
    const modal = document.getElementById('add-task-modal');
    modal.style.display = 'none';
    // Clear form when closing
    document.getElementById('add-task-form').reset();
}

function handleModalClick(event) {
    // Close modal when clicking outside the content area
    if (event.target.id === 'add-task-modal') {
        hideAddTaskModal();
    }
}

// Verification modal functions
function showVerificationModal(taskId, taskTitle, verified = true) {
    const modal = document.getElementById('verification-modal');
    const title = document.getElementById('verification-modal-title');
    const submitBtn = document.getElementById('verification-submit-btn');
    const verifiedInput = document.getElementById('verification-verified');
    
    document.getElementById('verification-task-id').value = taskId;
    verifiedInput.value = verified ? '1' : '0';
    
    if (verified) {
        title.textContent = `Verify Task: ${taskTitle}`;
        submitBtn.textContent = 'Verify Task';
        submitBtn.style.background = '#10b981';
    } else {
        title.textContent = `Reject Task: ${taskTitle}`;
        submitBtn.textContent = 'Reject Task';
        submitBtn.style.background = '#ef4444';
    }
    
    modal.style.display = 'flex';
    document.getElementById('verification-notes').value = '';
}

function hideVerificationModal() {
    const modal = document.getElementById('verification-modal');
    modal.style.display = 'none';
    document.getElementById('verification-form').reset();
}

function handleVerificationModalClick(event) {
    if (event.target.id === 'verification-modal') {
        hideVerificationModal();
    }
}

document.getElementById('add-task-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const title = document.getElementById('task-title').value;
    const description = document.getElementById('task-description').value;
    const assignedTo = document.getElementById('task-assignee').value;
    
    fetch('{{ route("chat.create-task", $trade->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            title: title,
            description: description,
            assigned_to: assignedTo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddTaskModal();
            addTaskToUI(data.task);
            updateTaskCount(); // Update task count after adding task
            // Clear form
            document.getElementById('task-title').value = '';
            document.getElementById('task-description').value = '';
            document.getElementById('task-assignee').value = '{{ $partner->id }}';
        } else {
            showError('Failed to create task: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
                    showError('Failed to create task. Please try again.');
    });
});

// Verification form handler
document.getElementById('verification-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const taskId = document.getElementById('verification-task-id').value;
    const verified = document.getElementById('verification-verified').value === '1';
    const verificationNotes = document.getElementById('verification-notes').value;
    
    fetch(`{{ url('/chat/task') }}/${taskId}/verify`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            verified: verified,
            verification_notes: verificationNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideVerificationModal();
            // Update the task in the UI
            updateTaskInUI(data.task);
            showSuccess(verified ? 'Task verified successfully!' : 'Task rejected successfully!');
            
            // Refresh skill learning status
            loadSkillLearningStatus();
        } else {
            showError('Failed to verify task: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to verify task. Please try again.');
    });
});

function addTaskToUI(task) {
    // Determine which container to add the task to based on who it's assigned to
    const isAssignedToMe = task.assigned_to == window.authUserId;
    const container = isAssignedToMe ? document.getElementById('my-tasks') : document.getElementById('partner-tasks');
    
    const taskDiv = document.createElement('div');
    taskDiv.className = 'task-item';
    taskDiv.setAttribute('data-task-id', task.id);
    taskDiv.style.cssText = 'margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;';
    
    const checkboxHtml = isAssignedToMe 
        ? `<input type="checkbox" ${task.completed ? 'checked' : ''} onchange="toggleTask(${task.id})" style="width: 16px; height: 16px;">`
        : `<input type="checkbox" disabled style="width: 16px; height: 16px;">`;
    
    taskDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            ${checkboxHtml}
            <span style="font-weight: 500;">${task.title}</span>
            ${task.completed ? '<span style="color: #10b981; font-size: 0.75rem; margin-left: auto;">✓ Completed</span>' : ''}
        </div>
        ${task.description ? `<div style="font-size: 0.875rem; color: #6b7280; margin-left: 24px;">${task.description}</div>` : ''}
    `;
    
    // Remove the "No tasks" message if it exists
    const noTasksMessage = container.querySelector('div[style*="text-align: center"]');
    if (noTasksMessage) {
        noTasksMessage.remove();
    }
    
    container.appendChild(taskDiv);
}

function updateTaskInUI(task) {
    const taskElement = document.querySelector(`[data-task-id="${task.id}"]`);
    if (!taskElement) return;
    
    // Update the task content with verification status
    const titleSpan = taskElement.querySelector('span[style*="font-weight: 500"]');
    const checkbox = taskElement.querySelector('input[type="checkbox"]');
    
    // Update checkbox state
    checkbox.checked = task.completed;
    
    // Update title styling
    if (task.completed) {
        titleSpan.style.textDecoration = 'line-through';
        titleSpan.style.color = '#6b7280';
    } else {
        titleSpan.style.textDecoration = 'none';
        titleSpan.style.color = '';
    }
    
    // Remove existing verification badge and actions
    const existingBadge = taskElement.querySelector('span[style*="background: #10b981"], span[style*="background: #f59e0b"]');
    const existingActions = taskElement.querySelector('div[style*="margin-top: 8px; margin-left: 24px"]');
    const existingNotes = taskElement.querySelector('div[style*="color: #059669"]');
    
    if (existingBadge) existingBadge.remove();
    if (existingActions) existingActions.remove();
    if (existingNotes) existingNotes.remove();
    
    // Add verification badge if completed
    if (task.completed) {
        const badge = document.createElement('span');
        if (task.verified) {
            badge.style.cssText = 'background: #10b981; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;';
            badge.textContent = '✓ Verified';
        } else {
            badge.style.cssText = 'background: #f59e0b; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;';
            badge.textContent = '⏳ Pending Verification';
        }
        
        const titleContainer = taskElement.querySelector('div[style*="display: flex; align-items: center"]');
        titleContainer.appendChild(badge);
    }
    
    // Add verification notes if verified and has notes
    if (task.verified && task.verification_notes) {
        const notesDiv = document.createElement('div');
        notesDiv.style.cssText = 'font-size: 0.875rem; color: #059669; margin-left: 24px; margin-top: 4px; font-style: italic;';
        notesDiv.innerHTML = `<strong>Verification:</strong> ${task.verification_notes}`;
        taskElement.appendChild(notesDiv);
    }
    
    // Add verification actions if completed, not verified, and user is creator
    if (task.completed && !task.verified && task.created_by == window.currentUserId) {
        const actionsDiv = document.createElement('div');
        actionsDiv.style.cssText = 'margin-top: 8px; margin-left: 24px;';
        actionsDiv.innerHTML = `
            <button class="verify-btn" data-task-id="${task.id}" data-task-title="${task.title}" data-verify="true"
                    style="background: #10b981; color: white; padding: 4px 12px; border: none; border-radius: 4px; font-size: 0.75rem; cursor: pointer; margin-right: 8px;">
                ✓ Verify
            </button>
            <button class="verify-btn" data-task-id="${task.id}" data-task-title="${task.title}" data-verify="false"
                    style="background: #ef4444; color: white; padding: 4px 12px; border: none; border-radius: 4px; font-size: 0.75rem; cursor: pointer;">
                ✗ Reject
            </button>
        `;
        taskElement.appendChild(actionsDiv);
    }
}

// Skill Learning Functions
async function loadSkillLearningStatus() {
    try {
        const response = await fetch('{{ route("chat.skill-learning-status", $trade->id) }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load skill learning status');
        }

        const data = await response.json();
        if (data.success) {
            updateSkillLearningStatusUI(data.summary);
        } else {
            throw new Error(data.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Error loading skill learning status:', error);
        document.getElementById('skill-learning-status').innerHTML = `
            <div style="text-align: center; color: #ef4444; font-size: 0.875rem;">
                Error loading skill learning status
            </div>
        `;
    }
}

function updateSkillLearningStatusUI(summary) {
    const statusContainer = document.getElementById('skill-learning-status');
    const completeSection = document.getElementById('complete-session-section');
    
    if (!summary.ready_for_processing) {
        statusContainer.innerHTML = `
            <div style="text-align: center; color: #6b7280; font-size: 0.875rem;">
                ${summary.message || 'Session not ready for completion'}
            </div>
        `;
        completeSection.style.display = 'none';
        return;
    }

    const tradeOwner = summary.trade_owner;
    const requester = summary.requester;
    
    let statusHTML = `
        <div style="space-y: 12px;">
            <div style="padding: 12px; background: white; border-radius: 6px; border: 1px solid #e5e7eb;">
                <div style="font-weight: 600; color: #374151; margin-bottom: 8px;">
                    ${tradeOwner.user.firstname} ${tradeOwner.user.lastname}
                </div>
                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 4px;">
                    Learning: <strong>${tradeOwner.skill_to_learn.name}</strong>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="flex: 1; background: #e5e7eb; border-radius: 4px; height: 6px;">
                        <div style="background: ${tradeOwner.completion_rate >= 100 ? '#10b981' : '#f59e0b'}; height: 100%; width: ${Math.min(tradeOwner.completion_rate, 100)}%; border-radius: 4px; transition: width 0.3s ease;"></div>
                    </div>
                    <span style="font-size: 0.75rem; font-weight: 600; color: ${tradeOwner.completion_rate >= 100 ? '#10b981' : '#f59e0b'};">
                        ${tradeOwner.completion_rate}%
                    </span>
                </div>
                <div style="font-size: 0.75rem; color: ${tradeOwner.will_receive_skill ? '#10b981' : '#6b7280'}; margin-top: 4px;">
                    ${tradeOwner.will_receive_skill ? '✅ Will receive skill' : '❌ Will not receive skill'}
                </div>
            </div>
            
            <div style="padding: 12px; background: white; border-radius: 6px; border: 1px solid #e5e7eb;">
                <div style="font-weight: 600; color: #374151; margin-bottom: 8px;">
                    ${requester.user.firstname} ${requester.user.lastname}
                </div>
                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 4px;">
                    Learning: <strong>${requester.skill_to_learn.name}</strong>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="flex: 1; background: #e5e7eb; border-radius: 4px; height: 6px;">
                        <div style="background: ${requester.completion_rate >= 100 ? '#10b981' : '#f59e0b'}; height: 100%; width: ${Math.min(requester.completion_rate, 100)}%; border-radius: 4px; transition: width 0.3s ease;"></div>
                    </div>
                    <span style="font-size: 0.75rem; font-weight: 600; color: ${requester.completion_rate >= 100 ? '#10b981' : '#f59e0b'};">
                        ${requester.completion_rate}%
                    </span>
                </div>
                <div style="font-size: 0.75rem; color: ${requester.will_receive_skill ? '#10b981' : '#6b7280'}; margin-top: 4px;">
                    ${requester.will_receive_skill ? '✅ Will receive skill' : '❌ Will not receive skill'}
                </div>
            </div>
        </div>
    `;
    
    statusContainer.innerHTML = statusHTML;
    
    // Show complete session button if both users have 100% completion or if any user has 100%
    const canComplete = tradeOwner.completion_rate >= 100 || requester.completion_rate >= 100;
    completeSection.style.display = canComplete ? 'block' : 'none';
    
    if (canComplete) {
        const completeBtn = document.getElementById('complete-session-btn');
        if (tradeOwner.completion_rate >= 100 && requester.completion_rate >= 100) {
            completeBtn.textContent = '✅ Complete Session & Learn Skills (Both Ready)';
            completeBtn.style.background = '#10b981';
        } else if (tradeOwner.completion_rate >= 100) {
            completeBtn.textContent = '✅ Complete Session (You\'re Ready)';
            completeBtn.style.background = '#f59e0b';
        } else {
            completeBtn.textContent = '✅ Complete Session (Partner Ready)';
            completeBtn.style.background = '#f59e0b';
        }
    }
}

async function completeSession() {
    const completeBtn = document.getElementById('complete-session-btn');
    const originalText = completeBtn.textContent;
    
    try {
        completeBtn.disabled = true;
        completeBtn.textContent = 'Processing...';
        
        const response = await fetch('{{ route("chat.complete-session", $trade->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showSuccess('Session completed successfully! Skills have been added to profiles.');
            
            // Update the skill learning status
            await loadSkillLearningStatus();
            
            // Update session status
            document.getElementById('session-status').textContent = '✅ Completed';
            document.getElementById('session-status').style.color = '#10b981';
            
            // Hide the complete session button
            document.getElementById('complete-session-section').style.display = 'none';
            
            // Show skill learning results
            if (data.skill_learning_results && data.skill_learning_results.messages) {
                data.skill_learning_results.messages.forEach(message => {
                    showSuccess(message);
                });
            }
        } else {
            showError(data.error || 'Failed to complete session');
            completeBtn.disabled = false;
            completeBtn.textContent = originalText;
        }
    } catch (error) {
        console.error('Error completing session:', error);
        showError('Failed to complete session. Please try again.');
        completeBtn.disabled = false;
        completeBtn.textContent = originalText;
    }
}

// Real-time clock and session duration timer
let sessionStart = new Date('{{ $trade->start_date }}');
let currentTimeElement = document.getElementById('current-time');
let sessionDurationElement = document.getElementById('session-duration');

// Update current time every second
let timeInterval = setInterval(function() {
    const now = new Date();
    currentTimeElement.textContent = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    // Calculate session duration
    const diff = Math.floor((now - sessionStart) / 60000);
    if (diff < 0) {
        sessionDurationElement.textContent = 'Not started yet';
    } else if (diff < 60) {
        sessionDurationElement.textContent = diff + ' minutes';
    } else {
        const hours = Math.floor(diff / 60);
        const minutes = diff % 60;
        sessionDurationElement.textContent = hours + 'h ' + minutes + 'm';
    }
}, 1000);

// Keep track of the last message count
let lastMessageCount = window.initialMessageCount;

// Check for new messages every 10 seconds (only if Laravel Echo is not working)
if (!window.Echo) {
    setInterval(checkForNewMessages, 1000);
}

function checkForNewMessages() {
    fetch('/chat/{{ $trade->id }}/messages')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > lastMessageCount) {
                // Get only the new messages
                const newMessages = data.messages.slice(lastMessageCount);
                lastMessageCount = data.count;

                // Add only new messages to chat
                newMessages.forEach(msg => {
                    addMessageToChat(
                        msg,
                        msg.sender.firstname + ' ' + msg.sender.lastname,
                        new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        msg.sender_id === window.authUserId
                    );
                });
            }
        })
        .catch(error => {
            console.error("Error checking for new messages:", error);
        });
}

function endSession() {
    // Check if there are any tasks before ending session
    const myTasks = document.querySelectorAll('#my-tasks .task-item').length;
    const partnerTasks = document.querySelectorAll('#partner-tasks .task-item').length;
    const totalTasks = myTasks + partnerTasks;
    
    if (totalTasks === 0) {
        const proceed = confirm('No tasks have been added to this session. Are you sure you want to end the session without any tasks?\n\nIt is recommended to add at least one task to track progress.');
        if (!proceed) {
            return;
        }
    } else {
        const proceed = confirm(`Session has ${totalTasks} task(s). Are you sure you want to end this session?`);
        if (!proceed) {
            return;
        }
    }
    
    // End the session
    if (confirm('Are you sure you want to end this session? This action cannot be undone.')) {
        // Here you could add an API call to mark the session as ended
        // For now, we'll just redirect
        window.location.href = '{{ route("trades.ongoing") }}';
    }
}

// ===== VIDEO CHAT FUNCTIONALITY =====

// WebRTC variables
let localStream = null;
let remoteStream = null;
let peerConnection = null;
let isCallActive = false;
let callStartTime = null;
let callTimer = null;
let isAudioMuted = false;
let isVideoOff = false;
let currentCallId = null;
let isInitiator = false;
let otherUserId = null;

// Video chat modal functions
function openVideoChat() {
    document.getElementById('video-chat-modal').style.display = 'flex';
    initializeVideoChat();
}

function closeVideoChat() {
    document.getElementById('video-chat-modal').style.display = 'none';
    if (isCallActive) {
        endVideoCall();
    }
    resetVideoChat();
}

function resetVideoChat() {
    // Reset UI
    if (isAutoCallEnabled) {
        document.getElementById('video-status').textContent = 'Camera and microphone ready. Auto-connecting when partner joins...';
    } else {
        document.getElementById('video-status').textContent = 'Initializing video chat...';
    }
    document.getElementById('call-timer').style.display = 'none';
    document.getElementById('start-call-btn').style.display = 'flex';
    document.getElementById('end-call-btn').style.display = 'none';
    document.getElementById('toggle-audio-btn').style.display = 'none';
    document.getElementById('toggle-video-btn').style.display = 'none';
    document.getElementById('mirror-video-btn').style.display = 'none';
    document.getElementById('screen-share-btn').style.display = 'none';
    document.getElementById('maximize-btn').style.display = 'none';
    document.getElementById('chat-toggle-btn').style.display = 'none';
    
    // Reset maximize state
    const videoGrid = document.getElementById('video-grid');
    const localVideoItem = document.getElementById('local-video-item');
    const remoteVideoItem = document.getElementById('remote-video-item');
    
    videoGrid.classList.remove('maximized');
    localVideoItem.classList.remove('maximized', 'minimized');
    remoteVideoItem.classList.remove('maximized', 'minimized');
    isMaximized = false;
    maximizedVideo = null;
    
    // Stop screen sharing if active
    if (isScreenSharing) {
        stopScreenShare();
    }
    
    // Reset status indicators
    document.getElementById('local-status').textContent = 'Local';
    document.getElementById('remote-status').textContent = 'Waiting...';
    document.getElementById('remote-status').className = 'connection-status';
    
    // Stop all tracks
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
    }
    
    // Clear video elements
    document.getElementById('local-video').srcObject = null;
    document.getElementById('remote-video').srcObject = null;
    
    // Reset variables
    isCallActive = false;
    isAudioMuted = false;
    isVideoOff = false;
    currentCallId = null;
    isInitiator = false;
    otherUserId = null;
    
    if (callTimer) {
        clearInterval(callTimer);
        callTimer = null;
    }
}

async function initializeVideoChat() {
    try {
        // Check if media devices are supported
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Media devices not supported');
        }
        
        // Request camera and microphone access
        localStream = await navigator.mediaDevices.getUserMedia({
            video: true,
            audio: true
        });
        
        // Display local video
        document.getElementById('local-video').srcObject = localStream;
        document.getElementById('local-status').textContent = 'Ready';
        document.getElementById('local-status').className = 'connection-status connected';
        
        // Update status
        document.getElementById('video-status').textContent = 'Camera and microphone ready. Auto-connecting when partner joins...';
        
        // Show start call button
        document.getElementById('start-call-btn').disabled = false;
        
    } catch (error) {
        console.error('Error accessing media devices:', error);
        let errorMessage = 'Error: Could not access camera or microphone. ';
        
        if (error.name === 'NotAllowedError') {
            errorMessage += 'Please allow camera and microphone access and refresh the page.';
        } else if (error.name === 'NotFoundError') {
            errorMessage += 'No camera or microphone found. Please check your devices.';
        } else if (error.name === 'NotSupportedError') {
            errorMessage += 'Your browser does not support video calls.';
        } else {
            errorMessage += 'Please check permissions and try again.';
        }
        
        document.getElementById('video-status').textContent = errorMessage;
        document.getElementById('start-call-btn').disabled = true;
    }
}

async function startVideoCall() {
    if (!localStream) {
        alert('Please wait for camera and microphone to initialize.');
        return;
    }
    
    // Generate unique call ID
    currentCallId = 'call_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    isInitiator = true;
    
    // Get the other user ID (partner)
    otherUserId = window.partnerId;
    
    console.log('Debug - Partner ID:', window.partnerId);
    console.log('Debug - Partner name:', window.partnerName);
    
    if (!otherUserId || otherUserId === 'null') {
        console.error('Unable to determine other user ID');
        console.error('Partner ID:', window.partnerId);
        document.getElementById('video-status').textContent = 'Error: Unable to start call. Please refresh the page.';
        return;
    }
    
    // Initialize WebRTC peer connection
    await initializePeerConnection();
    
    // Create and send offer
    try {
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        
        // Send offer to other user
        await sendVideoCallOffer(offer);
        
        // Update UI
        document.getElementById('video-status').textContent = 'Calling...';
        document.getElementById('start-call-btn').style.display = 'none';
        document.getElementById('end-call-btn').style.display = 'flex';
        document.getElementById('toggle-audio-btn').style.display = 'flex';
        document.getElementById('toggle-video-btn').style.display = 'flex';
        document.getElementById('mirror-video-btn').style.display = 'flex';
        document.getElementById('screen-share-btn').style.display = 'flex';
        document.getElementById('maximize-btn').style.display = 'flex';
        document.getElementById('chat-toggle-btn').style.display = 'flex';
        
        // Start call timer
        callStartTime = new Date();
        document.getElementById('call-timer').style.display = 'block';
        callTimer = setInterval(updateCallTimer, 1000);
        
        // Set connection timeout (45 seconds) - increased for better reliability
        let connectionTimeout = setTimeout(() => {
            if (isCallActive && peerConnection && peerConnection.connectionState !== 'connected') {
                console.warn('Connection timeout - ending call');
                document.getElementById('video-status').textContent = 'Connection timeout. Please try again.';
                endVideoCall();
            }
        }, 45000);
        
        // Clear timeout when connection is established
        const originalOnConnectionStateChange = peerConnection.onconnectionstatechange;
        peerConnection.onconnectionstatechange = () => {
            if (originalOnConnectionStateChange) {
                originalOnConnectionStateChange();
            }
            if (peerConnection.connectionState === 'connected') {
                clearTimeout(connectionTimeout);
            }
        };
        
        isCallActive = true;
        
    } catch (error) {
        console.error('Error creating offer:', error);
        document.getElementById('video-status').textContent = 'Error starting call. Please try again.';
        // Clear timeout on error
        if (typeof connectionTimeout !== 'undefined') {
            clearTimeout(connectionTimeout);
        }
    }
}

async function initializePeerConnection() {
    // Create RTCPeerConnection with STUN servers for NAT traversal
    const configuration = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' }
        ]
    };
    
    peerConnection = new RTCPeerConnection(configuration);
    
    // Add local stream tracks to peer connection
    localStream.getTracks().forEach(track => {
        peerConnection.addTrack(track, localStream);
    });
    
    // Handle incoming tracks
    peerConnection.ontrack = (event) => {
        console.log('Received remote stream');
        remoteStream = event.streams[0];
        document.getElementById('remote-video').srcObject = remoteStream;
        document.getElementById('remote-status').textContent = 'Connected';
        document.getElementById('remote-status').className = 'connection-status connected';
        document.getElementById('video-status').textContent = 'Call connected! You can now see and hear each other.';
    };
    
    // Handle ICE candidates
    peerConnection.onicecandidate = async (event) => {
        if (event.candidate) {
            console.log('ICE candidate generated:', event.candidate.type, event.candidate.protocol, event.candidate.address);
            // Only send ICE candidates if we have a valid call setup
            if (currentCallId && otherUserId) {
                // Send ICE candidate in background without blocking
                sendIceCandidate(event.candidate).catch(error => {
                    console.warn('ICE candidate send failed (non-critical):', error);
                });
            } else {
                console.warn('Skipping ICE candidate - call not properly initialized', {
                    currentCallId: currentCallId,
                    otherUserId: otherUserId
                });
            }
        } else {
            console.log('ICE gathering completed');
        }
    };
    
    // Handle connection state changes
    peerConnection.onconnectionstatechange = () => {
        console.log('Connection state:', peerConnection.connectionState);
        if (peerConnection.connectionState === 'connected') {
            document.getElementById('remote-status').textContent = 'Connected';
            document.getElementById('remote-status').className = 'connection-status connected';
            document.getElementById('video-status').textContent = 'Call connected! You can now see and hear each other.';
        } else if (peerConnection.connectionState === 'disconnected') {
            document.getElementById('remote-status').textContent = 'Disconnected';
            document.getElementById('remote-status').className = 'connection-status disconnected';
            document.getElementById('video-status').textContent = 'Connection lost. Attempting to reconnect...';
        } else if (peerConnection.connectionState === 'failed') {
            document.getElementById('remote-status').textContent = 'Connection Failed';
            document.getElementById('remote-status').className = 'connection-status disconnected';
            document.getElementById('video-status').textContent = 'Connection failed. Please try again.';
            // Auto-end call after failure
            setTimeout(() => {
                if (isCallActive) {
                    endVideoCall();
                }
            }, 3000);
        } else if (peerConnection.connectionState === 'connecting') {
            document.getElementById('video-status').textContent = 'Connecting...';
        }
    };
    
    // Handle ICE gathering state changes
    peerConnection.onicegatheringstatechange = () => {
        console.log('ICE gathering state:', peerConnection.iceGatheringState);
        if (peerConnection.iceGatheringState === 'complete') {
            console.log('ICE gathering completed');
        }
    };
    
    // Handle ICE connection state changes
    peerConnection.oniceconnectionstatechange = () => {
        console.log('ICE connection state:', peerConnection.iceConnectionState);
        if (peerConnection.iceConnectionState === 'failed') {
            console.error('ICE connection failed');
            document.getElementById('video-status').textContent = 'Connection failed. Please check your network and try again.';
        } else if (peerConnection.iceConnectionState === 'connected') {
            console.log('ICE connection established');
        }
    };
}

// Signaling functions
async function sendVideoCallOffer(offer) {
    try {
        // Generate absolute URL for production compatibility
        const baseUrl = window.location.origin;
        const url = baseUrl + '/chat/{{ $trade->id }}/video-call/offer';
        console.log('Offer URL:', url);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                offer: offer,
                callId: currentCallId
            }),
            credentials: 'same-origin' // Important for CORS
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        const result = await response.json();
        console.log('Offer sent successfully:', result);
    } catch (error) {
        console.error('Error sending offer:', error);
        document.getElementById('video-status').textContent = 'Error sending call. Please try again.';
    }
}

async function sendVideoCallAnswer(answer) {
    try {
        // Generate absolute URL for production compatibility
        const baseUrl = window.location.origin;
        const url = baseUrl + '/chat/{{ $trade->id }}/video-call/answer';
        console.log('Answer URL:', url);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                answer: answer,
                callId: currentCallId,
                toUserId: otherUserId
            }),
            credentials: 'same-origin' // Important for CORS
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        const result = await response.json();
        console.log('Answer sent successfully:', result);
    } catch (error) {
        console.error('Error sending answer:', error);
    }
}

async function sendIceCandidate(candidate, retryCount = 0) {
    const maxRetries = 3;
    const retryDelay = 1000; // 1 second
    
    try {
        // Validate required variables before making the request
        if (!otherUserId || !currentCallId) {
            console.warn('Cannot send ICE candidate: missing otherUserId or currentCallId', {
                otherUserId: otherUserId,
                currentCallId: currentCallId
            });
            return;
        }

        console.log('Sending ICE candidate to user:', otherUserId, 'for call:', currentCallId);
        
        // Generate absolute URL for production compatibility
        const baseUrl = window.location.origin;
        const url = baseUrl + '/chat/{{ $trade->id }}/video-call/ice-candidate';
        console.log('ICE candidate URL:', url);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                candidate: candidate,
                callId: currentCallId,
                toUserId: otherUserId
            }),
            credentials: 'same-origin' // Important for CORS
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        const result = await response.json();
        console.log('ICE candidate sent successfully:', result);
        return true;
        
    } catch (error) {
        console.error('Error sending ICE candidate:', error);
        
        // Retry logic for network errors
        if (retryCount < maxRetries && (error.name === 'TypeError' || error.message.includes('Failed to fetch'))) {
            console.log(`Retrying ICE candidate send (attempt ${retryCount + 1}/${maxRetries})...`);
            await new Promise(resolve => setTimeout(resolve, retryDelay * (retryCount + 1)));
            return sendIceCandidate(candidate, retryCount + 1);
        }
        
        // Don't throw the error to prevent breaking the WebRTC connection process
        // ICE candidates are not critical for connection establishment
        console.warn('ICE candidate sending failed after retries, continuing with connection...');
        return false;
    }
}

async function endVideoCall() {
    if (currentCallId) {
        try {
            // Generate absolute URL for production compatibility
            const baseUrl = window.location.origin;
            const url = baseUrl + '/chat/{{ $trade->id }}/video-call/end';
            console.log('End call URL:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    callId: currentCallId
                }),
                credentials: 'same-origin' // Important for CORS
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }
            
            const result = await response.json();
            console.log('Call ended successfully:', result);
        } catch (error) {
            console.error('Error ending call:', error);
        }
    }
    
    // Clean up local resources
    cleanupVideoCall();
}

function cleanupVideoCall() {
    isCallActive = false;
    
    // Close peer connection
    if (peerConnection) {
        peerConnection.close();
        peerConnection = null;
    }
    
    // Stop call timer
    if (callTimer) {
        clearInterval(callTimer);
        callTimer = null;
    }
    
    // Update UI
    document.getElementById('video-status').textContent = 'Call ended.';
    document.getElementById('call-timer').style.display = 'none';
    
    // Reset to initial state
    setTimeout(() => {
        if (document.getElementById('video-chat-modal').style.display !== 'none') {
            resetVideoChat();
            if (isAutoCallEnabled) {
                document.getElementById('video-status').textContent = 'Camera and microphone ready. Auto-connecting when partner joins...';
            } else {
                document.getElementById('video-status').textContent = 'Camera and microphone ready. Click "Start Call" to begin.';
            }
        }
    }, 2000);
}

// Video call event handlers
async function handleVideoCallOffer(data) {
    console.log('Handling video call offer from:', data.fromUserName);
    
    // Set up for incoming call
    currentCallId = data.callId;
    otherUserId = data.fromUserId;
    isInitiator = false;
    
    // Auto-accept if auto-call is enabled, otherwise show confirmation
    let acceptCall = false;
    
    if (isAutoCallEnabled) {
        acceptCall = true;
        console.log('Auto-accepting video call from:', data.fromUserName);
    } else {
        acceptCall = confirm(`${data.fromUserName} is calling you. Do you want to accept?`);
    }
    
    if (acceptCall) {
        // Initialize video chat if not already open
        if (document.getElementById('video-chat-modal').style.display === 'none') {
            openVideoChat();
        }
        
        // Wait for camera to be ready
        await new Promise(resolve => {
            const checkCamera = setInterval(() => {
                if (localStream) {
                    clearInterval(checkCamera);
                    resolve();
                }
            }, 100);
        });
        
        // Initialize peer connection
        await initializePeerConnection();
        
        // Set remote description
        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
        
        // Create and send answer
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        await sendVideoCallAnswer(answer);
        
        // Update UI
        if (isAutoCallEnabled) {
            document.getElementById('video-status').textContent = 'Auto-connected! Call in progress...';
        } else {
            document.getElementById('video-status').textContent = 'Call in progress...';
        }
        document.getElementById('start-call-btn').style.display = 'none';
        document.getElementById('end-call-btn').style.display = 'flex';
        document.getElementById('toggle-audio-btn').style.display = 'flex';
        document.getElementById('toggle-video-btn').style.display = 'flex';
        document.getElementById('mirror-video-btn').style.display = 'flex';
        document.getElementById('screen-share-btn').style.display = 'flex';
        document.getElementById('maximize-btn').style.display = 'flex';
        document.getElementById('chat-toggle-btn').style.display = 'flex';
        
        // Start call timer
        callStartTime = new Date();
        document.getElementById('call-timer').style.display = 'block';
        callTimer = setInterval(updateCallTimer, 1000);
        
        isCallActive = true;
    }
}

async function handleVideoCallAnswer(data) {
    console.log('Handling video call answer');
    
    try {
        if (peerConnection && peerConnection.remoteDescription === null) {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            console.log('Remote description set successfully');
            
            // Update UI to show connection is being established
            document.getElementById('video-status').textContent = 'Connecting...';
        } else {
            console.warn('Cannot set remote description - peer connection not ready or already set');
        }
    } catch (error) {
        console.error('Error handling video call answer:', error);
        document.getElementById('video-status').textContent = 'Error establishing connection. Please try again.';
    }
}

async function handleIceCandidate(data) {
    console.log('Handling ICE candidate');
    
    try {
        if (peerConnection && data.candidate) {
            await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            console.log('ICE candidate added successfully');
        } else {
            console.warn('Cannot add ICE candidate - peer connection not ready or no candidate data');
        }
    } catch (error) {
        console.error('Error handling ICE candidate:', error);
    }
}

// Debug function to check WebRTC connection status
function debugWebRTCConnection() {
    if (!peerConnection) {
        console.log('❌ No peer connection');
        return;
    }
    
    console.log('🔍 WebRTC Debug Info:');
    console.log('- Connection State:', peerConnection.connectionState);
    console.log('- ICE Connection State:', peerConnection.iceConnectionState);
    console.log('- ICE Gathering State:', peerConnection.iceGatheringState);
    console.log('- Signaling State:', peerConnection.signalingState);
    console.log('- Local Description:', peerConnection.localDescription);
    console.log('- Remote Description:', peerConnection.remoteDescription);
    console.log('- Current Call ID:', currentCallId);
    console.log('- Other User ID:', otherUserId);
    console.log('- Is Call Active:', isCallActive);
    
    // Check if we have local stream
    if (localStream) {
        console.log('- Local Stream Tracks:', localStream.getTracks().length);
        localStream.getTracks().forEach((track, index) => {
            console.log(`  Track ${index}:`, track.kind, track.enabled ? 'enabled' : 'disabled');
        });
    } else {
        console.log('❌ No local stream');
    }
    
    // Check if we have remote stream
    if (remoteStream) {
        console.log('- Remote Stream Tracks:', remoteStream.getTracks().length);
    } else {
        console.log('❌ No remote stream');
    }
}

// Add debug function to window for console access
window.debugWebRTC = debugWebRTCConnection;

// Additional debug function for video call troubleshooting
function debugVideoCallStatus() {
    console.log('🔍 Video Call Debug Status:');
    console.log('- Current Call ID:', currentCallId);
    console.log('- Other User ID:', otherUserId);
    console.log('- Is Call Active:', isCallActive);
    console.log('- Is Initiator:', isInitiator);
    console.log('- Local Stream:', localStream ? 'Available' : 'Not Available');
    console.log('- Remote Stream:', remoteStream ? 'Available' : 'Not Available');
    console.log('- Peer Connection:', peerConnection ? 'Active' : 'Not Active');
    
    if (peerConnection) {
        console.log('- Connection State:', peerConnection.connectionState);
        console.log('- ICE Connection State:', peerConnection.iceConnectionState);
        console.log('- ICE Gathering State:', peerConnection.iceGatheringState);
        console.log('- Signaling State:', peerConnection.signalingState);
    }
    
    console.log('- Window Location:', window.location.href);
    console.log('- Base URL:', window.location.origin);
    console.log('- Trade ID:', window.tradeId);
    console.log('- Auth User ID:', window.authUserId);
    console.log('- Partner ID:', window.partnerId);
}

// Add to window for console access
window.debugVideoCallStatus = debugVideoCallStatus;

function handleVideoCallEnd(data) {
    console.log('Handling video call end');
    
    // Show notification
    alert('The other person has ended the call.');
    
    // Clean up
    cleanupVideoCall();
}



function toggleMirror() {
    const localVideo = document.getElementById('local-video');
    const btn = document.getElementById('mirror-video-btn');
    
    if (localVideo.style.transform === 'scaleX(-1)') {
        // Remove mirror effect
        localVideo.style.transform = 'scaleX(1)';
        btn.textContent = '🪞';
        btn.title = 'Mirror Video';
        btn.style.background = '#6b7280';
    } else {
        // Apply mirror effect
        localVideo.style.transform = 'scaleX(-1)';
        btn.textContent = '🪞';
        btn.title = 'Unmirror Video';
        btn.style.background = '#10b981';
    }
}

function updateCallTimer() {
    if (callStartTime) {
        const now = new Date();
        const diff = Math.floor((now - callStartTime) / 1000);
        const minutes = Math.floor(diff / 60);
        const seconds = diff % 60;
        document.getElementById('call-timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
}

// ===== ENHANCED VIDEO CALL FEATURES =====

let isMaximized = false;
let maximizedVideo = null;
let isScreenSharing = false;
let screenStream = null;

function maximizeVideo(videoType) {
    const videoGrid = document.getElementById('video-grid');
    const localVideoItem = document.getElementById('local-video-item');
    const remoteVideoItem = document.getElementById('remote-video-item');
    
    if (isMaximized && maximizedVideo === videoType) {
        // Restore normal view
        videoGrid.classList.remove('maximized');
        localVideoItem.classList.remove('maximized');
        remoteVideoItem.classList.remove('maximized');
        localVideoItem.classList.remove('minimized');
        remoteVideoItem.classList.remove('minimized');
        isMaximized = false;
        maximizedVideo = null;
    } else {
        // Maximize selected video
        videoGrid.classList.add('maximized');
        
        if (videoType === 'local') {
            localVideoItem.classList.add('maximized');
            remoteVideoItem.classList.add('minimized');
            maximizedVideo = 'local';
        } else {
            remoteVideoItem.classList.add('maximized');
            localVideoItem.classList.add('minimized');
            maximizedVideo = 'remote';
        }
        
        isMaximized = true;
    }
}

function toggleMaximize() {
    // Toggle between normal and maximized view
    if (isMaximized) {
        const videoGrid = document.getElementById('video-grid');
        const localVideoItem = document.getElementById('local-video-item');
        const remoteVideoItem = document.getElementById('remote-video-item');
        
        videoGrid.classList.remove('maximized');
        localVideoItem.classList.remove('maximized', 'minimized');
        remoteVideoItem.classList.remove('maximized', 'minimized');
        isMaximized = false;
        maximizedVideo = null;
    } else {
        // Maximize remote video by default
        maximizeVideo('remote');
    }
}

async function toggleScreenShare() {
    const screenShareBtn = document.getElementById('screen-share-btn');
    
    try {
        if (!isScreenSharing) {
            // Start screen sharing
            screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: true,
                audio: true
            });
            
            // Replace local video stream with screen share
            const localVideo = document.getElementById('local-video');
            localVideo.srcObject = screenStream;
            
            // Update peer connection with screen stream
            if (peerConnection) {
                const videoTrack = screenStream.getVideoTracks()[0];
                const audioTrack = screenStream.getAudioTracks()[0];
                
                // Remove old tracks and add new ones
                const sender = peerConnection.getSenders().find(s => 
                    s.track && s.track.kind === 'video'
                );
                if (sender) {
                    await sender.replaceTrack(videoTrack);
                }
                
                if (audioTrack) {
                    const audioSender = peerConnection.getSenders().find(s => 
                        s.track && s.track.kind === 'audio'
                    );
                    if (audioSender) {
                        await audioSender.replaceTrack(audioTrack);
                    }
                }
            }
            
            isScreenSharing = true;
            screenShareBtn.classList.add('active');
            screenShareBtn.title = 'Stop Screen Share';
            
            // Handle screen share end
            screenStream.getVideoTracks()[0].onended = () => {
                stopScreenShare();
            };
            
        } else {
            // Stop screen sharing
            stopScreenShare();
        }
    } catch (error) {
        console.error('Error toggling screen share:', error);
        showError('Failed to start screen sharing. Please try again.');
    }
}

function stopScreenShare() {
    if (screenStream) {
        screenStream.getTracks().forEach(track => track.stop());
        screenStream = null;
    }
    
    // Restore local video stream
    if (localStream) {
        const localVideo = document.getElementById('local-video');
        localVideo.srcObject = localStream;
        
        // Update peer connection with original stream
        if (peerConnection) {
            const videoTrack = localStream.getVideoTracks()[0];
            const audioTrack = localStream.getAudioTracks()[0];
            
            const sender = peerConnection.getSenders().find(s => 
                s.track && s.track.kind === 'video'
            );
            if (sender) {
                sender.replaceTrack(videoTrack);
            }
            
            if (audioTrack) {
                const audioSender = peerConnection.getSenders().find(s => 
                    s.track && s.track.kind === 'audio'
                );
                if (audioSender) {
                    audioSender.replaceTrack(audioTrack);
                }
            }
        }
    }
    
    isScreenSharing = false;
    const screenShareBtn = document.getElementById('screen-share-btn');
    screenShareBtn.classList.remove('active');
    screenShareBtn.title = 'Share Screen';
}

function toggleChat() {
    // This could open a side chat panel in the video call
    // For now, we'll just show a notification
    showError('Chat feature in video call coming soon!');
}

// Enhanced toggle functions with better visual feedback
function toggleAudio() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            isAudioMuted = !audioTrack.enabled;
            
            const btn = document.getElementById('toggle-audio-btn');
            if (isAudioMuted) {
                btn.textContent = '🎤';
                btn.classList.add('muted');
                btn.title = 'Unmute Audio';
            } else {
                btn.textContent = '🎤';
                btn.classList.remove('muted');
                btn.title = 'Mute Audio';
            }
        }
    }
}

function toggleVideo() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            isVideoOff = !videoTrack.enabled;
            
            const btn = document.getElementById('toggle-video-btn');
            if (isVideoOff) {
                btn.textContent = '📹';
                btn.classList.add('muted');
                btn.title = 'Turn On Video';
            } else {
                btn.textContent = '📹';
                btn.classList.remove('muted');
                btn.title = 'Turn Off Video';
            }
        }
    }
}

// ===== AUTOMATIC VIDEO CALL FUNCTIONALITY =====
let isAutoCallEnabled = true;
let otherUserOnline = false;
let autoCallAttempted = false;
let presenceCheckInterval = null;

// User presence handlers
function handleUserJoined(data) {
    if (data.userId !== window.currentUserId) {
        otherUserOnline = true;
        updatePresenceStatus('online');
        
        // Auto-start video call if both users are online and auto-call is enabled
        if (isAutoCallEnabled && !isCallActive && !autoCallAttempted) {
            setTimeout(() => {
                startAutomaticVideoCall();
            }, 2000); // Wait 2 seconds for both users to be ready
        }
    }
}

function handleUserLeft(data) {
    if (data.userId !== window.currentUserId) {
        otherUserOnline = false;
        updatePresenceStatus('offline');
        
        // End call if other user leaves
        if (isCallActive) {
            endVideoCall();
        }
    }
}

function updatePresenceStatus(status) {
    const statusElement = document.getElementById('presence-status');
    if (statusElement) {
        if (status === 'online') {
            statusElement.textContent = '🟢 Partner is online';
            statusElement.style.color = '#10b981';
        } else {
            statusElement.textContent = '🔴 Partner is offline';
            statusElement.style.color = '#ef4444';
        }
    }
}

async function startAutomaticVideoCall() {
    if (autoCallAttempted || isCallActive || !otherUserOnline) {
        return;
    }
    
    autoCallAttempted = true;
    console.log('Starting automatic video call...');
    
    try {
        // Initialize video chat if not already done
        if (!localStream) {
            await initializeVideoChat();
        }
        
        // Start the call
        await startVideoCall();
        
        // Update status
        document.getElementById('video-status').textContent = 'Auto-connecting to video call...';
        
    } catch (error) {
        console.error('Error starting automatic video call:', error);
        document.getElementById('video-status').textContent = 'Auto-connection failed. You can start manually.';
        autoCallAttempted = false; // Allow retry
    }
}

function toggleAutoCall() {
    isAutoCallEnabled = !isAutoCallEnabled;
    const toggleBtn = document.getElementById('auto-call-toggle');
    if (toggleBtn) {
        if (isAutoCallEnabled) {
            toggleBtn.textContent = '🔗 Auto-call ON';
            toggleBtn.style.background = '#10b981';
        } else {
            toggleBtn.textContent = '🔗 Auto-call OFF';
            toggleBtn.style.background = '#6b7280';
        }
    }
}

// Send presence signal when page loads
function sendPresenceSignal() {
    if (window.Echo) {
        // Broadcast that this user joined the chat
        window.Echo.channel('trade-{{ $trade->id }}')
            .whisper('user-joined', {
                userId: window.currentUserId,
                userName: '{{ Auth::user()->firstname }}',
                timestamp: new Date().toISOString()
            });
    }
}

// Handle page unload to clean up video chat
window.addEventListener('beforeunload', () => {
    if (isCallActive) {
        endVideoCall();
    }
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    
    // Send user left signal
    if (window.Echo) {
        window.Echo.channel('trade-{{ $trade->id }}')
            .whisper('user-left', {
                userId: window.currentUserId,
                userName: '{{ Auth::user()->firstname }}',
                timestamp: new Date().toISOString()
            });
    }
});

// Initialize task count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTaskCount();
    initializeEmojiPicker();
    loadSkillLearningStatus();
    initializeProgressBars();
    initializeVerificationButtons();
    initializeAutoVideoCall();
});

function initializeAutoVideoCall() {
    // Send presence signal when page loads
    setTimeout(() => {
        sendPresenceSignal();
    }, 1000);
    
    // Initialize video chat automatically
    setTimeout(() => {
        initializeVideoChat();
    }, 2000);
}

function initializeVerificationButtons() {
    // Add event listeners to verification buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('verify-btn')) {
            const taskId = e.target.getAttribute('data-task-id');
            const taskTitle = e.target.getAttribute('data-task-title');
            const verify = e.target.getAttribute('data-verify') === 'true';
            showVerificationModal(taskId, taskTitle, verify);
        }
    });
}

function initializeProgressBars() {
    // Initialize my progress bar
    const myProgressBar = document.getElementById('my-progress-bar');
    const myProgressText = document.getElementById('my-progress-text');
    if (myProgressBar && myProgressText) {
        const progress = parseFloat(myProgressBar.getAttribute('data-progress'));
        myProgressBar.style.width = progress + '%';
        myProgressText.textContent = Math.round(progress) + '%';
    }
    
    // Initialize partner progress bar
    const partnerProgressBar = document.getElementById('partner-progress-bar');
    const partnerProgressText = document.getElementById('partner-progress-text');
    if (partnerProgressBar && partnerProgressText) {
        const progress = parseFloat(partnerProgressBar.getAttribute('data-progress'));
        partnerProgressBar.style.width = progress + '%';
        partnerProgressText.textContent = Math.round(progress) + '%';
    }
}

// ===== FILE UPLOAD FUNCTIONALITY =====
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) { // 10MB limit
            showError('Image file is too large. Please select an image smaller than 10MB.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            sendImageMessage(e.target.result, file.name);
        };
        reader.readAsDataURL(file);
    }
}

function handleVideoUpload(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 50 * 1024 * 1024) { // 50MB limit
            showError('Video file is too large. Please select a video smaller than 50MB.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            sendVideoMessage(e.target.result, file.name);
        };
        reader.readAsDataURL(file);
    }
}

function sendImageMessage(imageData, fileName) {
    const message = `[IMAGE:${fileName}]`;
    const messageInput = document.getElementById('message-input');
    messageInput.value = message;
    
    // Store the image data temporarily for sending
    window.tempImageData = imageData;
    window.tempFileName = fileName;
    
    // Send the message
    sendMessage(message);
}

function sendVideoMessage(videoData, fileName) {
    const message = `[VIDEO:${fileName}]`;
    const messageInput = document.getElementById('message-input');
    messageInput.value = message;
    
    // Store the video data temporarily for sending
    window.tempVideoData = videoData;
    window.tempFileName = fileName;
    
    // Send the message
    sendMessage(message);
}

// ===== EMOJI PICKER FUNCTIONALITY =====
function initializeEmojiPicker() {
    const emojiButton = document.getElementById('emoji-button');
    const messageInput = document.getElementById('message-input');
    
    if (!emojiButton || !messageInput) {
        console.log('Emoji picker elements not found');
        return;
    }
    
    // Create emoji picker modal
    const emojiModal = document.createElement('div');
    emojiModal.id = 'emoji-picker-modal';
    emojiModal.style.cssText = `
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    `;
    
    const emojiContainer = document.createElement('div');
    emojiContainer.style.cssText = `
        background: white;
        padding: 20px;
        border-radius: 12px;
        width: 300px;
        max-width: 90%;
        max-height: 400px;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    `;
    
    const emojiGrid = document.createElement('div');
    emojiGrid.style.cssText = `
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
        margin-bottom: 16px;
    `;
    
    // Common emojis
    const emojis = [
        '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃',
        '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜',
        '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟',
        '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠',
        '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗',
        '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧',
        '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧',
        '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕',
        '👇', '☝️', '👋', '🤚', '🖐️', '✋', '🖖', '👏', '🙌', '👐', '🤲', '🤝',
        '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃',
        '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕',
        '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️',
        '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈', '♉', '♊', '♋', '♌',
        '♍', '♎', '♏', '♐', '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️',
        '📴', '📳', '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️',
        '㊗️', '🈴', '🈵', '🈹', '🈲', '🅰️', '🅱️', '🆎', '🆑', '🅾️', '🆘', '❌',
        '⭕', '🛑', '⛔', '📛', '🚫', '💯', '💢', '♨️', '🚷', '🚯', '🚳', '🚱',
        '🔞', '📵', '🚭', '❗', '❕', '❓', '❔', '‼️', '⁉️', '🔅', '🔆', '〽️',
        '⚠️', '🚸', '🔱', '⚜️', '🔰', '♻️', '✅', '🈯', '💹', '❇️', '✳️', '❎',
        '🌐', '💠', 'Ⓜ️', '🌀', '💤', '🏧', '🚾', '♿', '🅿️', '🈳', '🈂️', '🛂',
        '🛃', '🛄', '🛅', '🚹', '🚺', '🚼', '🚻', '🚮', '🎦', '📶', '🈁', '🔣',
        'ℹ️', '🔤', '🔡', '🔠', '🆖', '🆗', '🆙', '🆒', '🆕', '🆓', '0️⃣', '1️⃣',
        '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'
    ];
    
    emojis.forEach(emoji => {
        const emojiButton = document.createElement('button');
        emojiButton.textContent = emoji;
        emojiButton.style.cssText = `
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        `;
        
        emojiButton.addEventListener('mouseenter', () => {
            emojiButton.style.backgroundColor = '#f3f4f6';
        });
        
        emojiButton.addEventListener('mouseleave', () => {
            emojiButton.style.backgroundColor = 'transparent';
        });
        
        emojiButton.addEventListener('click', () => {
            const currentValue = messageInput.value;
            const cursorPos = messageInput.selectionStart;
            const newValue = currentValue.slice(0, cursorPos) + emoji + currentValue.slice(cursorPos);
            messageInput.value = newValue;
            messageInput.focus();
            messageInput.setSelectionRange(cursorPos + emoji.length, cursorPos + emoji.length);
            emojiModal.style.display = 'none';
        });
        
        emojiGrid.appendChild(emojiButton);
    });
    
    const closeButton = document.createElement('button');
    closeButton.textContent = 'Close';
    closeButton.style.cssText = `
        width: 100%;
        padding: 8px;
        background: #6b7280;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    `;
    
    closeButton.addEventListener('click', () => {
        emojiModal.style.display = 'none';
    });
    
    emojiContainer.appendChild(emojiGrid);
    emojiContainer.appendChild(closeButton);
    emojiModal.appendChild(emojiContainer);
    document.body.appendChild(emojiModal);
    
    // Show emoji picker when button is clicked
    emojiButton.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        emojiModal.style.display = 'flex';
        console.log('Emoji picker opened');
    });
    
    // Close emoji picker when clicking outside
    emojiModal.addEventListener('click', (e) => {
        if (e.target === emojiModal) {
            emojiModal.style.display = 'none';
        }
    });
    
    // Add keyboard support for better accessibility
    emojiButton.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            emojiModal.style.display = 'flex';
        }
    });
    
    // Ensure emoji picker works on mobile devices
    if ('ontouchstart' in window) {
        // Mobile device - add touch support
        emojiButton.addEventListener('touchstart', (e) => {
            e.preventDefault();
            emojiModal.style.display = 'flex';
        });
    }
    
    console.log('Emoji picker initialized successfully');
}

// Cleanup function to clear all intervals and timeouts
function cleanupAllIntervals() {
    if (timeInterval) {
        clearInterval(timeInterval);
        timeInterval = null;
    }
    if (callTimer) {
        clearInterval(callTimer);
        callTimer = null;
    }
    // Clean up any other intervals or timeouts
    console.log('All intervals and timeouts cleaned up');
}

// Cleanup on page unload
window.addEventListener('beforeunload', cleanupAllIntervals);

// Cleanup on page visibility change (when user switches tabs)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden, could pause some operations
        console.log('Page hidden - pausing non-essential operations');
    } else {
        // Page is visible again
        console.log('Page visible - resuming operations');
    }
});

</script>
@endsection