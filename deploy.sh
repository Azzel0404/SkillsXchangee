#!/bin/bash

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Run seeders
echo "Running seeders..."
php artisan db:seed --force --no-interaction

# Cache configuration
echo "Caching configuration..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

# Start the server
echo "Starting PHP server..."
php -S 0.0.0.0:$PORT -t public
