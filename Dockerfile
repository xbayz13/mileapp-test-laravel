# Multi-stage build untuk Laravel dengan PHP 8.2 dan Node.js

# Stage 1: Build assets dengan Node.js
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy source files untuk build
COPY . .

# Build assets
RUN npm run build

# Stage 2: PHP application
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    autoconf \
    g++ \
    make \
    openssl-dev \
    bash

# Install PHP extensions
RUN docker-php-ext-install \
    zip \
    mbstring \
    exif \
    pcntl \
    bcmath \
    intl \
    opcache

# Install MongoDB extension
RUN pecl install mongodb && \
    docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for dependency caching
COPY composer*.json ./

# Copy application files (needed for composer post-install scripts)
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy built assets from node-builder stage
COPY --from=node-builder /app/public/build ./public/build

# Copy entrypoint script
COPY docker/entrypoint.sh /var/www/html/docker/entrypoint.sh
RUN chmod +x /var/www/html/docker/entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm"]
