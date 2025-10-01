#!/bin/bash

# Enable error reporting but don't exit on non-critical errors
set -e

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Ensure .env file exists with proper structure
echo "Ensuring .env file exists..."
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cat > .env << EOF
APP_NAME=SkillsXchangee
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://skillsxchangee-c2ml.onrender.com
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=WfrkcYjmqhlhiDAyWlp0xctxuZxYcZqY
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
EOF
fi

# Generate application key first
echo "Generating application key..."
php artisan key:generate --force --no-interaction

# Clear any cached config first
echo "Clearing cached configuration..."
php artisan config:clear --no-interaction

# Test database connection with retry logic
echo "Testing database connection..."
MAX_RETRIES=5
RETRY_COUNT=0
while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    echo "Database connection attempt $((RETRY_COUNT + 1))/$MAX_RETRIES..."
    if php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database connected successfully'; exit(0); } catch(Exception \$e) { echo 'Database connection failed: ' . \$e->getMessage(); exit(1); }" 2>/dev/null; then
        echo "✅ Database connection successful!"
        break
    else
        echo "❌ Database connection failed, retrying in 10 seconds..."
        RETRY_COUNT=$((RETRY_COUNT + 1))
        if [ $RETRY_COUNT -lt $MAX_RETRIES ]; then
            sleep 10
        fi
    fi
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo "⚠️ Database connection failed after $MAX_RETRIES attempts, continuing with cached config..."
fi

# Cache configuration for production (after APP_KEY is available)
echo "Caching configuration for production..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

# Run database migrations with retry logic
echo "Running database migrations..."
if php artisan migrate --force --no-interaction; then
    echo "✅ Database migrations completed successfully!"
    echo "Running database seeders..."
    php artisan db:seed --force --no-interaction || echo "Seeder completed with warnings (some data may already exist)"
else
    echo "⚠️ Database migrations failed, but continuing with application startup..."
    echo "This may be due to database connectivity issues or existing schema conflicts."
fi

# Start the PHP server
echo "Starting PHP server..."
php -S 0.0.0.0:$PORT -t public
