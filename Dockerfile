# Stage 1: Build frontend assets
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: PHP application
FROM serversideup/php:8.3-fpm-nginx

USER root

RUN install-php-extensions pdo_sqlite sqlite3 gd intl

# Install composer dependencies first (better layer caching)
COPY --chown=www-data:www-data composer.json composer.lock /var/www/html/
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY --chown=www-data:www-data . /var/www/html

# Copy built frontend assets from stage 1
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Ensure .env exists and storage/database are writable
RUN touch /var/www/html/.env \
    && mkdir -p /var/www/html/storage/framework/{cache,sessions,views} \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/database /var/www/html/bootstrap/cache

USER www-data
