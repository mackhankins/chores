FROM serversideup/php:8.3-fpm-nginx

# Switch to root for installing system packages
USER root

# Install SQLite support and other common Laravel dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    php8.3-sqlite3 \
    php8.3-gd \
    php8.3-intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory
ENV APP_BASE_DIR=/var/www/html

# Copy application files
COPY --chown=www-data:www-data . ${APP_BASE_DIR}

# Switch to www-data for running composer
USER www-data

# Install composer dependencies (no dev for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Make sure the SQLite database directory and storage are writable
RUN mkdir -p ${APP_BASE_DIR}/database \
    && touch ${APP_BASE_DIR}/database/database.sqlite \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache
