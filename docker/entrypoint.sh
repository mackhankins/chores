#!/bin/bash
set -e

cd /var/www/html

# Ensure database exists
touch database/database.sqlite
chown www-data:www-data database/database.sqlite

# Run migrations
php artisan migrate --force --no-interaction

# Start Apache
exec apache2-foreground
