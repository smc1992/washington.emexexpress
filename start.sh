#!/bin/sh

# Start PHP-FPM
php-fpm8 &

# Wait for PHP-FPM to start
sleep 2

# Start Nginx
nginx -g "daemon off;"
