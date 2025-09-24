# Pusher Deployment Checklist

## ✅ Pre-Deployment Setup

### 1. Environment Variables
Copy these to your `.env` file:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=2047345
PUSHER_APP_KEY=5c02e54d01ca577ae77e
PUSHER_APP_SECRET=3ad793a15a653af09cd6
PUSHER_APP_CLUSTER=ap1
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
VITE_PUSHER_APP_KEY=5c02e54d01ca577ae77e
VITE_PUSHER_APP_CLUSTER=ap1
VITE_PUSHER_HOST=
VITE_PUSHER_PORT=443
VITE_PUSHER_SCHEME=https
```

### 2. Required Commands
```bash
# Clear caches
php artisan config:clear
php artisan cache:clear

# Build assets
npm run build
```

## ✅ Verification Steps

### 1. Check Configuration
- [ ] `BROADCAST_DRIVER=pusher` is set
- [ ] All Pusher credentials are correct
- [ ] Vite environment variables are set

### 2. Test Real-time Features
- [ ] Open chat in two different browsers
- [ ] Send message from one browser
- [ ] Message appears instantly in other browser
- [ ] Connection status shows "Connected" (green dot)

### 3. Check Browser Console
- [ ] No JavaScript errors
- [ ] "Pusher connected successfully" message
- [ ] No connection errors

## ✅ Production Deployment

### 1. Environment Setup
- [ ] Add Pusher credentials to production `.env`
- [ ] Set `APP_DEBUG=false` for production
- [ ] Configure proper database connection

### 2. Asset Building
- [ ] Run `npm run build` for production assets
- [ ] Verify assets are generated in `public/build/`

### 3. Server Configuration
- [ ] Ensure WebSocket connections are allowed
- [ ] Configure proper CORS settings if needed
- [ ] Set up SSL certificate for HTTPS

## ✅ Monitoring

### 1. Pusher Dashboard
- [ ] Monitor message throughput
- [ ] Check connection counts
- [ ] Review error rates
- [ ] Monitor channel activity

### 2. Application Logs
- [ ] Check Laravel logs for broadcasting errors
- [ ] Monitor connection issues
- [ ] Review authentication failures

## ✅ Troubleshooting

### Common Issues
1. **Messages not appearing in real-time**
   - Check browser console for errors
   - Verify Pusher credentials
   - Ensure `BROADCAST_DRIVER=pusher`

2. **Connection errors**
   - Check Pusher app status
   - Verify cluster setting (ap1)
   - Check network connectivity

3. **Authentication errors**
   - Ensure user is logged in
   - Verify trade access permissions
   - Check channel authorization

## ✅ Security Checklist

- [ ] Channel authorization is working
- [ ] Only trade participants can access chats
- [ ] User authentication is required
- [ ] Messages are validated before broadcasting

## ✅ Performance Optimization

- [ ] Monitor Pusher usage limits
- [ ] Optimize message payload size
- [ ] Implement message pagination if needed
- [ ] Consider connection pooling for high traffic

---

**Quick Copy Commands:**
```bash
# Copy environment variables to .env
# Then run:
php artisan config:clear && php artisan cache:clear && npm run build
```
