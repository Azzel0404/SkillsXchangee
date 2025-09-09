#!/bin/bash

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Start the PHP server
echo "Starting PHP server..."
php -S 0.0.0.0:$PORT -t public
