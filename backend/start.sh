#!/bin/bash

# Enable error reporting but don't exit on non-critical errors
set -e

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Test database connection (non-blocking)
echo "Testing database connection..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database connected successfully'; } catch(Exception \$e) { echo 'Database connection failed: ' . \$e->getMessage(); }" || echo "Database connection test failed, but continuing..."

# Clear any cached config first
echo "Clearing cached configuration..."
php artisan config:clear --no-interaction

# Cache configuration for production (after APP_KEY is available)
echo "Caching configuration for production..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Run database seeders (only if not already seeded)
echo "Running database seeders..."
php artisan db:seed --force --no-interaction || echo "Seeder completed with warnings (some data may already exist)"

# Start the PHP server
echo "Starting PHP server..."
php -S 0.0.0.0:$PORT -t public
