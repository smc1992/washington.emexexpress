#!/bin/sh

echo "Starting Emex Express Chicago Container..."

# Fix permissions
chown -R nginx:nginx /var/www/html /var/log/php

# Start PHP-FPM in background
echo "Starting PHP-FPM on port 9000..."
php-fpm --daemonize

# Wait for PHP-FPM to start
echo "Waiting for PHP-FPM to start..."
sleep 5

# Test PHP-FPM connection
echo "Testing PHP-FPM connection..."
nc -z 127.0.0.1 9000 && echo "PHP-FPM is running" || echo "PHP-FPM failed to start"

# Test PHP
echo "Testing PHP installation..."
php -v

# Start Nginx in foreground
echo "Starting Nginx..."
nginx -g "daemon off;"
