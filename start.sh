#!/bin/sh

# Create PHP-FPM socket directory
mkdir -p /run/php

# Fix permissions
chown -R nginx:nginx /var/www/html /run/php /var/log/php

# Start PHP-FPM in background with explicit config
php-fpm -y /etc/php/php-fpm.conf -F &

# Wait for PHP-FPM to create socket
sleep 3

# Check if socket exists
if [ ! -S /run/php/php-fpm.sock ]; then
    echo "PHP-FPM socket not found, checking processes..."
    ps aux | grep php-fpm
    echo "Starting PHP-FPM with alternative method..."
    php-fpm8 -y /etc/php8/php-fpm.conf -F &
    sleep 2
fi

# Test PHP-FPM
echo "Testing PHP-FPM..."
php -v

# Start Nginx in foreground
echo "Starting Nginx..."
nginx -g "daemon off;"
