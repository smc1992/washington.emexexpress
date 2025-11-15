#!/bin/sh

# Start PHP-FPM (try different commands for Alpine compatibility)
php-fpm8 || php-fpm || /usr/sbin/php-fpm8 &

# Wait for PHP-FPM to start
sleep 2

# Start Nginx
nginx -g "daemon off;"
