#!/bin/bash

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Run database seeders (only if not already seeded)
echo "Running database seeders..."
php artisan db:seed --force --no-interaction

# Clear and cache configuration for production
echo "Optimizing for production..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

# Start the PHP server
echo "Starting PHP server..."
php -S 0.0.0.0:$PORT -t public
