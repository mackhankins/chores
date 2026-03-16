#!/bin/bash
set -e

cd /var/www/html

# Ensure database exists
touch database/database.sqlite

# Run migrations
php artisan migrate --force --no-interaction 2>&1

# Pass through to the original PHP Docker entrypoint
exec docker-php-entrypoint "$@"
