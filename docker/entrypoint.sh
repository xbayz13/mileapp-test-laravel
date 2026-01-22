#!/bin/sh

set -e

echo "Waiting for MongoDB to be ready..."
# Wait for MongoDB using PHP MongoDB driver
until php -r "try { \$m = new MongoDB\Driver\Manager('mongodb://mongodb:27017'); \$m->executeCommand('admin', new MongoDB\Driver\Command(['ping' => 1])); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
  echo "MongoDB is unavailable - sleeping"
  sleep 2
done

echo "MongoDB is up - executing command"

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --optimize-autoloader --no-interaction
fi

# Create .env if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
    fi
fi

# Generate application key if not set
php artisan key:generate --force || true

# Clear and cache config
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run migrations
php artisan migrate --force || true

# Set permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

echo "Application is ready!"

# Execute
exec "$@"
