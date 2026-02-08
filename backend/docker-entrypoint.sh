#!/bin/sh
set -e

# Install dependencies if vendor is missing
if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader --no-dev
fi

# Generate app key if not set
if [ -z "$(grep '^APP_KEY=base64:' .env 2>/dev/null)" ]; then
    php artisan key:generate --force
fi

# Fix storage permissions
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "Starting PHP-FPM..."
exec php-fpm
