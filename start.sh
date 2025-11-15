#!/bin/sh

echo "=========================================="
echo "Starting Emex Express Chicago Container"
echo "=========================================="

# Fix permissions
echo "Setting permissions..."
chown -R nginx:nginx /var/www/html /var/log/php

# Show system info
echo "System info:"
echo "PHP version: $(php -v | head -1)"
echo "Nginx version: $(nginx -v 2>&1)"
echo "Available PHP-FPM configs:"
ls -la /etc/php*/php-fpm.d/www.conf 2>/dev/null || echo "No PHP-FPM config found"

# Start PHP-FPM with explicit config
echo ""
echo "Starting PHP-FPM..."
php-fpm --fpm-config /etc/php/php-fpm.conf --daemonize

# Wait and check if PHP-FPM is running
echo "Waiting for PHP-FPM to start..."
sleep 5

echo ""
echo "Checking processes:"
ps aux | grep php-fpm | grep -v grep || echo "PHP-FPM process not found"

echo ""
echo "Testing PHP-FPM connection..."
if nc -z 127.0.0.1 9000; then
    echo "✅ PHP-FPM is listening on port 9000"
else
    echo "❌ PHP-FPM is NOT listening on port 9000"
    echo "Trying alternative start method..."
    php-fpm8 --daemonize
    sleep 3
    if nc -z 127.0.0.1 9000; then
        echo "✅ PHP-FPM8 started successfully"
    else
        echo "❌ PHP-FPM failed to start completely"
    fi
fi

echo ""
echo "Testing PHP installation..."
php -r "echo 'PHP test: OK\n';"

echo ""
echo "Starting Nginx..."
nginx -g "daemon off;"
