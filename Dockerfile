# Stage 1: Build Vue/Inertia frontend assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: PHP-FPM server for Laravel
FROM php:8.2-fpm-alpine AS fpm_server
WORKDIR /var/www/html

# Install system dependencies and PHP extensions for Laravel, Meilisearch, and MySQL
RUN apk add --no-cache libpng-dev libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the Laravel codebase
COPY . .

# Copy the compiled Vue assets from the Stage 1 frontend build
COPY --from=frontend /app/public/build ./public/build

# Install PHP production dependencies
RUN composer install --optimize-autoloader --no-dev

# Set correct permissions for Laravel's cache and storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
