@extends('layouts.chat')

@section('content')
<style>
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes flash {
    0% { background-color: rgba(59, 130, 246, 0.3); }
    50% { background-color: rgba(16, 185, 129, 0.5); }
    100% { background-color: rgba(59, 130, 246, 0.3); }
}

.flash-effect {
    animation: flash 0.5s ease-in-out;
}

/* Ensure all message bubbles have proper text wrapping */
#chat-messages > div > div {
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
}

#chat-messages > div > div > div:first-child {
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
    background: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.video-chat-container {
    background: white;
    border-radius: 12px;
    padding: 20px;
    max-width: 800px;
    width: 90%;
    max-height: 90%;
    overflow: hidden;
    position: relative;
}

.video-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.video-item {
    position: relative;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 16/9;
}

.video-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-item.remote {
    border: 2px solid #3b82f6;
}

.video-item.local {
    border: 2px solid #10b981;
}

.video-controls {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.video-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.video-btn.primary {
    background: #3b82f6;
    color: white;
}

.video-btn.primary:hover {
    background: #2563eb;
}

.video-btn.danger {
    background: #ef4444;
    color: white;
}

.video-btn.danger:hover {
    background: #dc2626;
}

.video-btn.success {
    background: #10b981;
    color: white;
}

.video-btn.success:hover {
    background: #059669;
}

.video-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.video-status {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
    color: #374151;
}

.call-timer {
    text-align: center;
    font-size: 1.2rem;
    font-weight: 600;
    color: #3b82f6;
    margin-bottom: 20px;
}

.close-video {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    font-size: 1.2rem;
}

.connection-status {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.connection-status.connected {
    background: #10b981;
    color: white;
}

.connection-status.connecting {
    background: #f59e0b;
    color: white;
}

.connection-status.disconnected {
    background: #ef4444;
    color: white;
}
</style>

<!-- Video Chat Modal -->
<div id="video-chat-modal" class="video-chat-modal">
    <div class="video-chat-container">
        <button class="close-video" onclick="closeVideoChat()">×</button>
        
        <div class="video-status" id="video-status">Initializing video chat...</div>
        <div class="call-timer" id="call-timer" style="display: none;">00:00</div>
        
        <div class="video-grid">
            <div class="video-item local">
                <video id="local-video" autoplay muted playsinline></video>
                <div class="connection-status" id="local-status">Local</div>
            </div>
            <div class="video-item remote">
                <video id="remote-video" autoplay playsinline></video>
                <div class="connection-status" id="remote-status">Waiting...</div>
            </div>
        </div>
        
        <div class="video-controls">
            <button id="start-call-btn" class="video-btn primary" onclick="startVideoCall()">Start Call</button>
            <button id="end-call-btn" class="video-btn danger" onclick="endVideoCall()" style="display: none;">End Call</button>
            <button id="toggle-audio-btn" class="video-btn success" onclick="toggleAudio()" style="display: none;">Mute Audio</button>
            <button id="toggle-video-btn" class="video-btn success" onclick="toggleVideo()" style="display: none;">Turn Off Video</button>
        </div>
    </div>
</div>

<div style="height: 100vh; display: flex; flex-direction: column;">
    <!-- Header -->
    <div style="background: #1e40af; color: white; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 1.5rem; font-weight: bold;">SkillsXchange</div>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: #ef4444; text-decoration: none;">Logout</a>
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
            <div style="background: #1e40af; color: white; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>💬</span>
                    <span>Session Chat</span>
                    <span id="new-message-indicator" style="display: none; background: #ef4444; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem; animation: pulse 2s infinite;">NEW</span>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button id="video-call-btn" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;" onclick="openVideoChat()">📷</button>
                    <button style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">🎤</button>
                    <button style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">⚠️</button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages" style="flex: 1; padding: 16px; overflow-y: auto; background: #f9fafb;">
                @foreach($messages as $message)
                    <div style="margin-bottom: 16px; display: flex; {{ $message->sender_id === Auth::id() ? 'justify-content: flex-end' : 'justify-content: flex-start' }};">
                        <div style="max-width: 70%; {{ $message->sender_id === Auth::id() ? 'background: #3b82f6; color: white;' : 'background: #e5e7eb; color: #374151;' }} padding: 12px; border-radius: 12px; position: relative; word-wrap: break-word; overflow-wrap: break-word;">
                            <div style="margin-bottom: 4px; word-break: break-word; line-height: 1.4;">{{ $message->message }}</div>
                            <div style="font-size: 0.75rem; opacity: 0.8;">{{ $message->created_at->format('g:i A') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Message Input -->
            <div style="padding: 16px; background: white; border-top: 1px solid #e5e7eb;">
                <form id="message-form" style="display: flex; gap: 8px;">
                    <input type="text" id="message-input" placeholder="Type your message here..." 
                           style="flex: 1; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; outline: none;">
                    <button type="submit" id="send-button" style="background: #1e40af; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Send</button>
                </form>

            </div>
        </div>

        <!-- Session Tasks (Right Sidebar) -->
        <div style="width: 350px; background: white; border-left: 1px solid #e5e7eb; display: flex; flex-direction: column;">
            <!-- Sidebar Header -->
            <div style="background: #f3f4f6; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 8px;">
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
                            <div class="task-item" data-task-id="{{ $task->id }}" style="margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                    <input type="checkbox" {{ $task->completed ? 'checked' : '' }} 
                                           onchange="toggleTask({{ $task->id }})" 
                                           style="width: 16px; height: 16px;">
                                    <span style="font-weight: 500; {{ $task->completed ? 'text-decoration: line-through; color: #6b7280;' : '' }}">{{ $task->title }}</span>
                                </div>
                                @if($task->description)
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-left: 24px;">{{ $task->description }}</div>
                                @endif
                            </div>
                        @empty
                            <div style="color: #6b7280; font-size: 0.875rem; text-align: center; padding: 16px;">No tasks assigned to you</div>
                        @endforelse
                    </div>
                    
                    <!-- Your Progress -->
                    <div style="margin-top: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.875rem; color: #6b7280;">Progress</span>
                            <span style="font-size: 0.875rem; font-weight: 600;">{{ round($myProgress) }}%</span>
                        </div>
                        <div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div style="background: #10b981; height: 100%; width: {{ $myProgress }}%; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                </div>

                <!-- Partner's Tasks -->
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 12px; color: #374151;">{{ $partner->firstname }}'s Tasks</h3>
                    <div id="partner-tasks">
                        @forelse($partnerTasks as $task)
                            <div class="task-item" data-task-id="{{ $task->id }}" style="margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                    <input type="checkbox" {{ $task->completed ? 'checked' : '' }} disabled style="width: 16px; height: 16px;">
                                    <span style="font-weight: 500; {{ $task->completed ? 'text-decoration: line-through; color: #6b7280;' : '' }}">{{ $task->title }}</span>
                                </div>
                                @if($task->description)
                                    <div style="font-size: 0.875rem; color: #6b7280; margin-left: 24px;">{{ $task->description }}</div>
                                @endif
                            </div>
                        @empty
                            <div style="color: #6b7280; font-size: 0.875rem; text-align: center; padding: 16px;">No tasks assigned to {{ $partner->firstname }}</div>
                        @endforelse
                    </div>
                    
                    <!-- Partner's Progress -->
                    <div style="margin-top: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.875rem; color: #6b7280;">Progress</span>
                            <span style="font-size: 0.875rem; font-weight: 600;">{{ round($partnerProgress) }}%</span>
                        </div>
                        <div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div style="background: #3b82f6; height: 100%; width: {{ $partnerProgress }}%; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                </div>

                <!-- Add Task Button -->
                <button onclick="showAddTaskModal()" style="width: 100%; background: #1e40af; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    + Add Task
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="background: #f3f4f6; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e5e7eb;">
        <div style="font-size: 0.875rem; color: #6b7280;">
            Session started: Today at {{ now()->format('g:i A') }} • Duration: <span id="session-duration">0 minutes</span>
        </div>
        <button onclick="endSession()" style="background: #ef4444; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">End Session</button>
    </div>
</div>

<!-- Add Task Modal -->
<div id="add-task-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
    <div style="background: white; padding: 24px; border-radius: 8px; width: 400px; max-width: 90%;">
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 16px;">Add Task for {{ $partner->firstname }}</h3>
        <form id="add-task-form">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 4px; font-weight: 500;">Task Title</label>
                <input type="text" id="task-title" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 4px; font-weight: 500;">Description (Optional)</label>
                <textarea id="task-description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; resize: vertical;"></textarea>
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" onclick="hideAddTaskModal()" style="padding: 8px 16px; border: 1px solid #d1d5db; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 16px; background: #1e40af; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Task</button>
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

// Listen for events using Laravel Echo
if (window.Echo) {
    // Listen for new messages
    window.Echo.channel('trade-{{ $trade->id }}')
        .listen('new-message', function(data) {
            console.log('Received new message event:', data);
            // Only add if it's not from the current user (to avoid duplicates)
            if (data.message.sender_id !== {{ Auth::id() }}) {
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
    // Show loading state
    const sendButton = document.getElementById('send-button');
    const originalText = sendButton.textContent;
    sendButton.textContent = 'Sending...';
    sendButton.disabled = true;
    sendButton.style.background = '#6b7280';
    
    // Add message to UI immediately (optimistic update)
    const tempId = 'temp_' + Date.now();
    addMessageToChat(message, '{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}', new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}), true, tempId);
    
    fetch('{{ route("chat.send-message", $trade->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        sendButton.textContent = originalText;
        sendButton.disabled = false;
        sendButton.style.background = '#1e40af';
        
        if (data.success) {
            // Update the temporary message with the real one and mark it as confirmed
            updateMessageInChat(tempId, data.message);
            // Mark this message as confirmed to prevent duplicate Echo events
            const messageElement = document.querySelector(`[data-temp-id="${tempId}"]`);
            if (messageElement) {
                messageElement.setAttribute('data-confirmed', 'true');
                messageElement.removeAttribute('data-temp-id');
            }
        } else {
            // Remove the temporary message if it failed
            removeMessageFromChat(tempId);
            showError('Failed to send message: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        // Reset button state
        sendButton.textContent = originalText;
        sendButton.disabled = false;
        sendButton.style.background = '#1e40af';
        
        // Remove the temporary message if it failed
        removeMessageFromChat(tempId);
        showError('Failed to send message. Please try again.');
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
    
    messageDiv.innerHTML = `
        <div style="max-width: 70%; ${isOwn ? 'background: #3b82f6; color: white;' : 'background: #e5e7eb; color: #374151;'} padding: 12px; border-radius: 12px; position: relative; word-wrap: break-word; overflow-wrap: break-word;">
            <div style="margin-bottom: 4px; word-break: break-word; line-height: 1.4;">${messageText}</div>
            <div style="font-size: 0.75rem; opacity: 0.8;">${messageTime}</div>
        </div>
    `;
    
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
}

// Modal handling
function showAddTaskModal() {
    document.getElementById('add-task-modal').style.display = 'flex';
}

function hideAddTaskModal() {
    document.getElementById('add-task-modal').style.display = 'none';
}

document.getElementById('add-task-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const title = document.getElementById('task-title').value;
    const description = document.getElementById('task-description').value;
    
    fetch('{{ route("chat.create-task", $trade->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            title: title,
            description: description,
            assigned_to: {{ $partner->id }}
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddTaskModal();
            addTaskToUI(data.task);
            // Clear form
            document.getElementById('task-title').value = '';
            document.getElementById('task-description').value = '';
        } else {
            showError('Failed to create task: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
                    showError('Failed to create task. Please try again.');
    });
});

function addTaskToUI(task) {
    const partnerTasksContainer = document.getElementById('partner-tasks');
    const taskDiv = document.createElement('div');
    taskDiv.className = 'task-item';
    taskDiv.setAttribute('data-task-id', task.id);
    taskDiv.style.cssText = 'margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;';
    
    taskDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <input type="checkbox" disabled style="width: 16px; height: 16px;">
            <span style="font-weight: 500;">${task.title}</span>
        </div>
        ${task.description ? `<div style="font-size: 0.875rem; color: #6b7280; margin-left: 24px;">${task.description}</div>` : ''}
    `;
    
    // Remove the "No tasks" message if it exists
    const noTasksMessage = partnerTasksContainer.querySelector('div[style*="text-align: center"]');
    if (noTasksMessage) {
        noTasksMessage.remove();
    }
    
    partnerTasksContainer.appendChild(taskDiv);
}

// Session duration timer
let sessionStart = new Date();
setInterval(function() {
    const now = new Date();
    const diff = Math.floor((now - sessionStart) / 60000);
    document.getElementById('session-duration').textContent = diff + ' minutes';
}, 60000);

// Keep track of the last message count
let lastMessageCount = {{ $messages->count() }};

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
                        msg.sender_id === {{ Auth::id() }}
                    );
                });
            }
        })
        .catch(error => {
            console.error("Error checking for new messages:", error);
        });
}

function endSession() {
    if (confirm('Are you sure you want to end this session?')) {
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
    document.getElementById('video-status').textContent = 'Initializing video chat...';
    document.getElementById('call-timer').style.display = 'none';
    document.getElementById('start-call-btn').style.display = 'inline-block';
    document.getElementById('end-call-btn').style.display = 'none';
    document.getElementById('toggle-audio-btn').style.display = 'none';
    document.getElementById('toggle-video-btn').style.display = 'none';
    
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
    
    if (callTimer) {
        clearInterval(callTimer);
        callTimer = null;
    }
}

async function initializeVideoChat() {
    try {
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
        document.getElementById('video-status').textContent = 'Camera and microphone ready. Click "Start Call" to begin.';
        
        // Show start call button
        document.getElementById('start-call-btn').disabled = false;
        
    } catch (error) {
        console.error('Error accessing media devices:', error);
        document.getElementById('video-status').textContent = 'Error: Could not access camera or microphone. Please check permissions.';
        document.getElementById('start-call-btn').disabled = true;
    }
}

function startVideoCall() {
    if (!localStream) {
        alert('Please wait for camera and microphone to initialize.');
        return;
    }
    
    // Initialize WebRTC peer connection
    initializePeerConnection();
    
    // Update UI
    document.getElementById('video-status').textContent = 'Call in progress...';
    document.getElementById('start-call-btn').style.display = 'none';
    document.getElementById('end-call-btn').style.display = 'inline-block';
    document.getElementById('toggle-audio-btn').style.display = 'inline-block';
    document.getElementById('toggle-video-btn').style.display = 'inline-block';
    
    // Start call timer
    callStartTime = new Date();
    document.getElementById('call-timer').style.display = 'block';
    callTimer = setInterval(updateCallTimer, 1000);
    
    isCallActive = true;
    
    // Simulate remote connection (in real implementation, this would connect to the other user)
    setTimeout(() => {
        simulateRemoteConnection();
    }, 2000);
}

function initializePeerConnection() {
    // Create RTCPeerConnection with STUN servers for NAT traversal
    const configuration = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ]
    };
    
    peerConnection = new RTCPeerConnection(configuration);
    
    // Add local stream tracks to peer connection
    localStream.getTracks().forEach(track => {
        peerConnection.addTrack(track, localStream);
    });
    
    // Handle incoming tracks
    peerConnection.ontrack = (event) => {
        remoteStream = event.streams[0];
        document.getElementById('remote-video').srcObject = remoteStream;
        document.getElementById('remote-status').textContent = 'Connected';
        document.getElementById('remote-status').className = 'connection-status connected';
    };
    
    // Handle connection state changes
    peerConnection.onconnectionstatechange = () => {
        console.log('Connection state:', peerConnection.connectionState);
        if (peerConnection.connectionState === 'connected') {
            document.getElementById('remote-status').textContent = 'Connected';
            document.getElementById('remote-status').className = 'connection-status connected';
        } else if (peerConnection.connectionState === 'disconnected') {
            document.getElementById('remote-status').textContent = 'Disconnected';
            document.getElementById('remote-status').className = 'connection-status disconnected';
        }
    };
}

function simulateRemoteConnection() {
    // This is a simulation - in a real app, you'd connect to the actual other user
    document.getElementById('remote-status').textContent = 'Connected (Demo)';
    document.getElementById('remote-status').className = 'connection-status connected';
    document.getElementById('video-status').textContent = 'Call connected! You can now see and hear each other.';
}

function endVideoCall() {
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
            document.getElementById('video-status').textContent = 'Camera and microphone ready. Click "Start Call" to begin.';
        }
    }, 2000);
}

function toggleAudio() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            isAudioMuted = !audioTrack.enabled;
            
            const btn = document.getElementById('toggle-audio-btn');
            if (isAudioMuted) {
                btn.textContent = 'Unmute Audio';
                btn.style.background = '#6b7280';
            } else {
                btn.textContent = 'Mute Audio';
                btn.style.background = '#10b981';
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
                btn.textContent = 'Turn On Video';
                btn.style.background = '#6b7280';
            } else {
                btn.textContent = 'Turn Off Video';
                btn.style.background = '#10b981';
            }
        }
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

// Handle page unload to clean up video chat
window.addEventListener('beforeunload', () => {
    if (isCallActive) {
        endVideoCall();
    }
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
});

</script>
@endsection
