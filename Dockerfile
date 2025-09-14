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

# Copy only necessary backend files for Railway (API only)
COPY backend/composer.json backend/composer.lock ./
COPY backend/package.json backend/package-lock.json ./
COPY backend/artisan ./
COPY backend/start.sh ./
COPY backend/app/ ./app/
COPY backend/bootstrap/ ./bootstrap/
COPY backend/config/ ./config/
COPY backend/database/ ./database/
COPY backend/public/ ./public/
COPY backend/resources/ ./resources/
COPY backend/routes/ ./routes/
COPY backend/storage/ ./storage/
COPY backend/vite.config.js ./
COPY backend/tailwind.config.js ./
COPY backend/postcss.config.js ./
# Dependencies will be installed fresh during build

# Create basic .env file for build process
RUN cp .env.example .env || echo "APP_NAME=SkillsXchangee\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false" > .env

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install

# Build assets
RUN npm run build

# Application key will be generated at runtime in start.sh

# Expose port
EXPOSE $PORT

# Make start script executable
RUN chmod +x start.sh

# Start the application
CMD ./start.sh
