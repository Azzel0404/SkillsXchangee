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

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install

# Copy public assets to ensure they're available
RUN cp -r public/* /var/www/html/public/ || true

# Generate application key
RUN php artisan key:generate --force --no-interaction

# Expose port
EXPOSE $PORT

# Make start script executable
RUN chmod +x start.sh

# Start the application
CMD ./start.sh
