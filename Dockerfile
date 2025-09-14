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

# Create basic .env file for build process
RUN cp .env.example .env || echo "APP_NAME=SkillsXchangee\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false" > .env

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install

# Copy public assets to ensure they're available
RUN cp -r public/* /var/www/html/public/ || true

# Application key will be generated at runtime in start.sh

# Expose port
EXPOSE $PORT

# Make start script executable
RUN chmod +x start.sh

# Start the application
CMD ./start.sh
