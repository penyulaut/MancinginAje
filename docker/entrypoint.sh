#!/bin/sh
set -e

echo "=========================================="
echo "MancinginAje - Starting Application"
echo "=========================================="

# Verify PHP extensions
echo "Checking PHP extensions..."
php -m | grep -i pdo
php -m | grep -i pgsql

# Clear any stale cache
echo "Clearing cached configuration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear 2>/dev/null || true

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Create storage symlink for public file uploads
echo "Creating storage symlink..."
mkdir -p storage/app/public
php artisan storage:link --force 2>/dev/null || true

echo "=========================================="
echo "Application is ready!"
echo "Access: http://localhost:2310"
echo "=========================================="

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
