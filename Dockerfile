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
    libzip-dev \
    netcat-openbsd \
    linux-headers \
    postgresql-dev

# Install PHP extensions including PostgreSQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache

# Verify PostgreSQL extension is loaded
RUN php -m | grep -i pgsql

# Install Redis extension
RUN apk add --no-cache autoconf g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make linux-headers

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Copy built frontend assets from frontend stage
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# Remove any existing config cache from local
RUN rm -f bootstrap/cache/config.php \
    && rm -f bootstrap/cache/routes-v7.php \
    && rm -f bootstrap/cache/services.php \
    && rm -f bootstrap/cache/packages.php

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
    && mkdir -p database \
    && mkdir -p /var/log/php

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache database

# Expose port
EXPOSE 2310

# Create entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
