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
# Ensure storage and cache dirs exist and have correct permissions
# Note: sh does not support brace expansion, so create directories individually
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache resources/views || true
chmod -R 775 storage bootstrap/cache || true
php artisan config:clear
php artisan route:clear
# Ensure view path exists (avoid error when path missing)
if [ ! -d "resources/views" ]; then
    echo "resources/views not found, creating..."
    mkdir -p resources/views
    chown -R www-data:www-data resources/views || true
fi
php artisan view:clear || echo "Skipping view:clear (no views)"
php artisan cache:clear 2>/dev/null || true

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache || echo "config:cache failed, continuing"
php artisan route:cache || echo "route:cache failed, continuing"
# view:cache may fail when no views present; do not stop startup
php artisan view:cache || echo "view:cache failed or no views, continuing"

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
php artisan migrate --force || echo "migrate failed, continuing"
# Run seeders but do not fail startup if seeding hits duplicates
php artisan db:seed --force || echo "db:seed failed or partially applied, continuing"

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
