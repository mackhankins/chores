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

ENV APP_BASE_DIR=/var/www/html

COPY --chown=www-data:www-data . ${APP_BASE_DIR}

# Copy built frontend assets from stage 1
COPY --from=frontend --chown=www-data:www-data /app/public/build ${APP_BASE_DIR}/public/build

# Ensure .env file exists (runtime env vars will override)
RUN touch ${APP_BASE_DIR}/.env

# Ensure storage structure exists and is writable
RUN mkdir -p ${APP_BASE_DIR}/storage/framework/{cache,sessions,views} \
    && mkdir -p ${APP_BASE_DIR}/storage/logs \
    && mkdir -p ${APP_BASE_DIR}/database \
    && touch ${APP_BASE_DIR}/database/database.sqlite \
    && chown -R www-data:www-data ${APP_BASE_DIR}/storage ${APP_BASE_DIR}/database

USER www-data

RUN composer install --no-dev --optimize-autoloader --no-interaction
