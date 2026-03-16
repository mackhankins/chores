#!/bin/sh
set -e

# Ensure SQLite database exists
touch /var/www/html/database/database.sqlite

# Run migrations
php /var/www/html/artisan migrate --force --no-interaction

# Execute the original command
exec "$@"
