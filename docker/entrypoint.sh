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

# Wait for database to be ready (if using networked DB)
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-5432}
echo "Waiting for database at $DB_HOST:$DB_PORT..."
until nc -z "$DB_HOST" "$DB_PORT"; do
    echo "Database not ready, sleeping 1s..."
    sleep 1
done

# Run migrations and seeders
echo "Running migrations and seeders..."
php artisan migrate --force
php artisan db:seed --force

# Create storage symlink for public file uploads
echo "Creating storage symlink..."
mkdir -p storage/app/public/images
chmod -R 775 storage/app/public
chown -R www-data:www-data storage/app/public
php artisan storage:link --force 2>/dev/null || true

# Verify symlink
if [ -L /var/www/html/public/storage ]; then
    echo "Storage symlink created successfully"
    ls -la /var/www/html/public/storage
else
    echo "WARNING: Storage symlink may not have been created properly"
    # Fallback: create symlink manually
    ln -sf /var/www/html/storage/app/public /var/www/html/public/storage
    echo "Manual symlink created"
fi

echo "=========================================="
echo "Application is ready!"
echo "Access: http://localhost:2310"
echo "=========================================="

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
