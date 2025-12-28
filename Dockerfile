# Build stage for frontend assets
FROM node:20-alpine AS frontend

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci

# Copy source files for build
COPY . .

# Build frontend assets
RUN npm run build

# PHP Application stage
FROM php:8.2-fpm-alpine AS app

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    icu-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd intl zip opcache

# Install Redis extension
RUN apk add --no-cache autoconf g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Copy built frontend assets from frontend stage
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-progress

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Create necessary directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && mkdir -p database

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Create SQLite database if needed
RUN touch database/database.sqlite \
    && chown www-data:www-data database/database.sqlite \
    && chmod 775 database/database.sqlite

# Expose port
EXPOSE 2310

# Create entrypoint script
RUN echo '#!/bin/sh' > /entrypoint.sh && \
    echo 'set -e' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Wait for MySQL if using MySQL' >> /entrypoint.sh && \
    echo 'if [ "$DB_CONNECTION" = "mysql" ]; then' >> /entrypoint.sh && \
    echo '    echo "Waiting for MySQL..."' >> /entrypoint.sh && \
    echo '    while ! nc -z $DB_HOST $DB_PORT; do' >> /entrypoint.sh && \
    echo '        sleep 1' >> /entrypoint.sh && \
    echo '    done' >> /entrypoint.sh && \
    echo '    echo "MySQL is ready!"' >> /entrypoint.sh && \
    echo 'fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Run migrations' >> /entrypoint.sh && \
    echo 'php artisan config:cache' >> /entrypoint.sh && \
    echo 'php artisan route:cache' >> /entrypoint.sh && \
    echo 'php artisan view:cache' >> /entrypoint.sh && \
    echo 'php artisan migrate --force' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Start supervisor' >> /entrypoint.sh && \
    echo 'exec /usr/bin/supervisord -c /etc/supervisord.conf' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

# Install netcat for health checks
RUN apk add --no-cache netcat-openbsd

ENTRYPOINT ["/entrypoint.sh"]
