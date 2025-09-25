#!/bin/bash

# SkillsXchange Render Deployment Script with WebSocket Support
echo "Starting SkillsXchange on Render with WebSocket support..."

# Set proper permissions
chmod -R 755 storage bootstrap/cache

# Clear and cache configurations
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start WebSocket server in background
echo "Starting WebSocket signaling server on port 8080..."
php artisan websocket:start --port=8080 &
WEBSOCKET_PID=$!

# Function to cleanup on exit
cleanup() {
    echo "Cleaning up processes..."
    if [ ! -z "$WEBSOCKET_PID" ]; then
        kill $WEBSOCKET_PID 2>/dev/null || true
    fi
    exit 0
}

# Set trap to cleanup on script exit
trap cleanup EXIT INT TERM

# Wait a moment for WebSocket server to start
sleep 2

# Start the main application
echo "Starting main application on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT
