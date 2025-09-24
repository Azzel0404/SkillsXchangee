#!/bin/bash

# SkillsXchange Render Deployment Script
echo "Starting SkillsXchange on Render..."

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

# Start the application
php artisan serve --host=0.0.0.0 --port=$PORT
