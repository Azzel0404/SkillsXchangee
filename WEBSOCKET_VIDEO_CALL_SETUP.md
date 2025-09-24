# WebSocket Video Call Setup Guide

This guide will help you set up the WebSocket-based video call feature with ICE candidate exchange.

## Overview

The new implementation uses:
- **WebSocket** for real-time signaling (ICE candidates, offers, answers)
- **WebRTC** for peer-to-peer video/audio communication
- **ICE servers** for NAT traversal
- **Enhanced error handling** and connection management

## Prerequisites

1. PHP 8.0 or higher
2. Composer
3. Node.js (for development)
4. Modern web browser with WebRTC support

## Installation Steps

### 1. Install Required Dependencies

```bash
# Install PHP WebSocket dependencies
composer require ratchet/pawl react/socket

# Install Node.js dependencies (if not already installed)
npm install
```

### 2. Register the WebSocket Command

Add the following to your `app/Console/Kernel.php` in the `$commands` array:

```php
protected $commands = [
    // ... existing commands
    \App\Console\Commands\StartWebSocketServer::class,
];
```

### 3. Start the WebSocket Server

#### Option A: Using the provided scripts

**Linux/Mac:**
```bash
chmod +x start-websocket-server.sh
./start-websocket-server.sh 8080
```

**Windows:**
```cmd
start-websocket-server.bat 8080
```

#### Option B: Using Artisan command directly

```bash
php artisan websocket:start --port=8080
```

### 4. Update Your Frontend

Replace your existing video call JavaScript with the new WebSocket implementation:

```html
<!-- Include the new WebSocket video call manager -->
<script src="{{ asset('js/websocket-video-call.js') }}"></script>
```

Or copy the content from `resources/views/websocket-video-call.js` to your existing file.

### 5. Update Your Blade Templates

Make sure your Blade templates include the necessary global variables:

```html
<script>
    window.userId = {{ auth()->id() }};
    window.tradeId = {{ $trade->id }};
    window.partnerId = {{ $partner->id }};
</script>
```

## Testing

### 1. Test the WebSocket Server

Open two browser tabs and navigate to `test-websocket-video-call.html`:

1. In the first tab, set:
   - User ID: `user1`
   - Trade ID: `trade1`
   - Partner ID: `user2`

2. In the second tab, set:
   - User ID: `user2`
   - Trade ID: `trade1`
   - Partner ID: `user1`

3. Click "Setup Test Environment" in both tabs
4. Click "Start Call" in one tab
5. Accept the call in the other tab

### 2. Test in Your Application

1. Start the WebSocket server
2. Navigate to your video call page
3. Test the video call functionality

## Configuration

### WebSocket Server Configuration

The WebSocket server runs on port 8080 by default. You can change this by:

1. Modifying the port in the startup scripts
2. Using the `--port` parameter with the Artisan command
3. Updating the WebSocket URL in the JavaScript code

### ICE Server Configuration

The implementation includes multiple STUN servers for better connectivity:

```javascript
this.iceServers = [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
    // ... more servers
];
```

For production, consider adding TURN servers for better NAT traversal.

## Features

### Enhanced Connection Management

- **Automatic reconnection** with exponential backoff
- **Connection state monitoring** (WebSocket, local, remote)
- **Timeout handling** for failed connections
- **Retry logic** for ICE connection failures

### Improved Error Handling

- **Detailed logging** for debugging
- **User-friendly error messages**
- **Graceful degradation** when WebSocket is unavailable

### Better ICE Candidate Handling

- **Real-time ICE candidate exchange** via WebSocket
- **Multiple STUN servers** for better connectivity
- **Enhanced ICE gathering** with detailed logging

## Troubleshooting

### Common Issues

1. **WebSocket connection failed**
   - Check if the WebSocket server is running
   - Verify the port is not blocked by firewall
   - Check browser console for errors

2. **Video call not connecting**
   - Ensure both users have camera/microphone permissions
   - Check if STUN servers are accessible
   - Verify WebSocket signaling is working

3. **ICE candidates not exchanging**
   - Check WebSocket connection status
   - Verify the signaling server is running
   - Check browser console for WebSocket errors

### Debug Mode

Enable debug logging by checking the browser console and the debug log section in the test page.

## Production Deployment

### 1. Use a Process Manager

For production, use a process manager like PM2 or Supervisor:

```bash
# Using PM2
pm2 start "php artisan websocket:start --port=8080" --name "websocket-server"
```

### 2. Configure Reverse Proxy

If using Nginx, add WebSocket support:

```nginx
location /ws {
    proxy_pass http://localhost:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
}
```

### 3. Use TURN Servers

For production, configure TURN servers for better NAT traversal:

```javascript
this.iceServers = [
    { urls: 'stun:stun.l.google.com:19302' },
    { 
        urls: 'turn:your-turn-server.com:3478',
        username: 'your-username',
        credential: 'your-password'
    }
];
```

## Security Considerations

1. **Authentication**: Ensure users are authenticated before joining rooms
2. **Authorization**: Verify users can only join authorized trade rooms
3. **Rate Limiting**: Implement rate limiting for WebSocket messages
4. **Input Validation**: Validate all incoming WebSocket messages

## Performance Optimization

1. **Connection Pooling**: Limit the number of concurrent connections
2. **Message Queuing**: Implement message queuing for high-traffic scenarios
3. **Resource Cleanup**: Ensure proper cleanup of connections and resources
4. **Monitoring**: Monitor WebSocket server performance and connection counts

## Support

For issues or questions:

1. Check the debug logs in the browser console
2. Verify WebSocket server is running and accessible
3. Test with the provided test page
4. Check network connectivity and firewall settings
