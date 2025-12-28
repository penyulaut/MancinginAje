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

# Install system dependencies including PostgreSQL
RUN apk add --no-cache \
    git \
    curl \
    curl-dev \
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
    postgresql-dev \
    postgresql-libs \
    ca-certificates \
    openssl

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp

# Install each extension separately for better error handling
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_sqlite
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install exif
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install gd
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip
RUN docker-php-ext-install opcache
RUN docker-php-ext-install curl

# Verify critical extensions are loaded
RUN php -m | grep -i pdo_pgsql || (echo "pdo_pgsql not installed!" && exit 1)
RUN php -m | grep -i curl || (echo "curl not installed!" && exit 1)

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
