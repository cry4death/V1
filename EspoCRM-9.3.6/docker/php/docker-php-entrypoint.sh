#!/bin/sh
set -e
if [ -d /var/www/html/data ]; then
    chown -R www-data:www-data /var/www/html/data 2>/dev/null || true
    chmod -R ug+rwX /var/www/html/data 2>/dev/null || true
fi
exec docker-php-entrypoint "$@"
