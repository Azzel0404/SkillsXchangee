# 🎥 Video Call Troubleshooting Guide

## 🚨 Common Issues & Solutions

### **Issue: "Calling..." Status Persists - No Connection**

**Symptoms:**
- Call shows "Calling..." with timer running
- Other party cannot be seen or heard
- Connection never establishes

**Possible Causes & Solutions:**

#### **1. Network/Firewall Issues**
```bash
# Check if STUN servers are accessible
# Test in browser console:
navigator.mediaDevices.getUserMedia({video: true, audio: true})
  .then(stream => console.log('Media access OK'))
  .catch(err => console.error('Media access failed:', err));
```

**Solutions:**
- Check firewall settings
- Ensure ports 3478 (STUN) are open
- Try different network (mobile hotspot)
- Use VPN if behind corporate firewall

#### **2. Browser Compatibility**
**Supported Browsers:**
- ✅ Chrome 56+
- ✅ Firefox 52+
- ✅ Safari 11+
- ✅ Edge 79+

**Solutions:**
- Update browser to latest version
- Enable WebRTC in browser settings
- Try incognito/private mode
- Clear browser cache and cookies

#### **3. Pusher/WebSocket Connection Issues**
**Check in browser console:**
```javascript
// Check if Echo is connected
console.log('Echo connected:', window.Echo.connector.socket.connected);

// Check Pusher connection
console.log('Pusher state:', window.Echo.connector.pusher.connection.state);
```

**Solutions:**
- Check internet connection
- Verify Pusher credentials in `.env`
- Restart the application
- Check browser WebSocket support

#### **4. WebRTC Signaling Issues**
**Debug in browser console:**
```javascript
// Run debug function
debugWebRTC();

// Check specific states
console.log('Connection State:', peerConnection.connectionState);
console.log('ICE State:', peerConnection.iceConnectionState);
```

**Solutions:**
- Check if offer/answer exchange is working
- Verify ICE candidates are being exchanged
- Check for JavaScript errors in console

### **Issue: Camera/Microphone Not Working**

**Symptoms:**
- Camera shows black screen
- No audio input/output
- Permission denied errors

**Solutions:**
1. **Grant Permissions:**
   - Click camera icon in browser address bar
   - Allow camera and microphone access
   - Refresh page and try again

2. **Check Device Availability:**
   ```javascript
   // Check available devices
   navigator.mediaDevices.enumerateDevices()
     .then(devices => {
       devices.forEach(device => {
         console.log(device.kind, device.label);
       });
     });
   ```

3. **Device Conflicts:**
   - Close other applications using camera/microphone
   - Unplug and reconnect USB devices
   - Restart browser

### **Issue: Poor Video/Audio Quality**

**Solutions:**
1. **Network Optimization:**
   - Use wired connection instead of WiFi
   - Close bandwidth-heavy applications
   - Check internet speed (minimum 1 Mbps)

2. **Browser Settings:**
   - Disable hardware acceleration
   - Clear browser cache
   - Update browser

3. **Device Settings:**
   - Ensure good lighting for video
   - Use external microphone if available
   - Close unnecessary applications

## 🔧 Debugging Steps

### **Step 1: Check Browser Console**
Open Developer Tools (F12) and look for:
- ❌ JavaScript errors
- ❌ WebRTC connection failures
- ❌ Pusher connection issues
- ❌ Permission denied errors

### **Step 2: Test WebRTC Functionality**
```javascript
// Test basic WebRTC support
if (window.RTCPeerConnection) {
  console.log('✅ WebRTC supported');
} else {
  console.log('❌ WebRTC not supported');
}

// Test media access
navigator.mediaDevices.getUserMedia({video: true, audio: true})
  .then(stream => {
    console.log('✅ Media access OK');
    stream.getTracks().forEach(track => track.stop());
  })
  .catch(err => console.error('❌ Media access failed:', err));
```

### **Step 3: Check Network Connectivity**
```javascript
// Test STUN server connectivity
const pc = new RTCPeerConnection({
  iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
});

pc.onicecandidate = (event) => {
  if (event.candidate) {
    console.log('✅ STUN server reachable');
  }
};

pc.createDataChannel('test');
pc.createOffer().then(offer => pc.setLocalDescription(offer));
```

### **Step 4: Verify Signaling**
Check if events are being received:
```javascript
// Listen for WebRTC events
window.Echo.channel('trade-123')
  .listen('video-call-offer', (data) => console.log('Offer received:', data))
  .listen('video-call-answer', (data) => console.log('Answer received:', data))
  .listen('video-call-ice-candidate', (data) => console.log('ICE candidate received:', data));
```

## 🛠️ Advanced Troubleshooting

### **Connection Timeout Issues**
If calls timeout after 30 seconds:
1. Check firewall settings
2. Verify STUN server accessibility
3. Test with different network
4. Check for proxy/VPN interference

### **ICE Candidate Exchange Issues**
If ICE candidates aren't being exchanged:
1. Check Pusher connection
2. Verify event listeners are working
3. Check for JavaScript errors
4. Ensure both users are online

### **Audio/Video Sync Issues**
If audio and video are out of sync:
1. Check device drivers
2. Update browser
3. Restart browser
4. Check system performance

## 📱 Mobile-Specific Issues

### **iOS Safari**
- Ensure iOS 11+ for WebRTC support
- Check if camera permissions are granted
- Try Chrome or Firefox on iOS

### **Android Chrome**
- Update Chrome to latest version
- Check camera/microphone permissions
- Try incognito mode

## 🔄 Reset Procedures

### **Complete Reset**
1. End current call
2. Refresh browser page
3. Clear browser cache
4. Restart browser
5. Try again

### **Application Reset**
1. Stop Laravel application
2. Clear Laravel cache: `php artisan cache:clear`
3. Restart application
4. Test again

## 📞 Getting Help

If issues persist:
1. Run `debugWebRTC()` in browser console
2. Copy console output
3. Check network connectivity
4. Test with different browsers/devices
5. Contact support with debug information

## 🧪 Test Scenarios

### **Local Testing**
1. Open two browser windows
2. Use different user accounts
3. Start video call from one window
4. Accept from other window
5. Verify connection

### **Network Testing**
1. Test on same network
2. Test on different networks
3. Test mobile to desktop
4. Test with VPN
5. Test with different ISPs

---

**Remember:** Most WebRTC issues are network-related. Always check network connectivity and firewall settings first!
