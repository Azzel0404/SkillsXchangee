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

# Set working directory to backend
WORKDIR /var/www/html

# Copy Laravel application files
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./
COPY artisan ./
COPY start.sh ./
COPY app/ ./app/
COPY bootstrap/ ./bootstrap/
COPY config/ ./config/
COPY database/ ./database/
COPY public/ ./public/
COPY resources/ ./resources/
COPY routes/ ./routes/
COPY storage/ ./storage/
# Dependencies will be installed fresh during build

# Create basic .env file for build process
RUN cp .env.example .env || echo "APP_NAME=SkillsXchangee\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false" > .env

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install

# Build assets (with error handling for Laravel projects)
RUN npm run build 2>/dev/null || echo "Asset build skipped - Laravel project without index.html"

# Application key will be generated at runtime in start.sh

# Expose port
EXPOSE $PORT

# Make start script executable
RUN chmod +x start.sh

# Start the application
CMD ./start.sh
