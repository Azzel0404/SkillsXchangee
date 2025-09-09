FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Create .env file from .env.example (or create a basic one if .env.example doesn't exist)
RUN if [ -f .env.example ]; then cp .env.example .env; else echo "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=laravel\nDB_USERNAME=root\nDB_PASSWORD=\nCACHE_DRIVER=file\nSESSION_DRIVER=file\nQUEUE_CONNECTION=sync" > .env; fi

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install
RUN npm run build

# Generate application key
RUN php artisan key:generate --force --no-interaction

# Expose port
EXPOSE $PORT

# Start the application
CMD php -S 0.0.0.0:$PORT -t public
