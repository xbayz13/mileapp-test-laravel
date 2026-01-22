#!/bin/bash

set -e

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Installing Node dependencies..."
npm ci

echo "Building frontend assets..."
npm run build

echo "Caching Laravel configuration..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Build completed successfully!"
