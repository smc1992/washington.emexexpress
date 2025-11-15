# Ultra-minimalist Dockerfile for guaranteed deployment
FROM nginx:alpine

# Install PHP and essential extensions only
RUN apk add --no-cache \
    php \
    php-fpm \
    php-mbstring \
    php-xml \
    php-curl \
    php-json \
    php-openssl \
    composer \
    && rm -rf /var/cache/apk/*

# Simple PHP-FPM configuration
RUN echo "listen = 127.0.0.1:9000" >> /etc/php8/php-fpm.d/www.conf && \
    echo "clear_env = no" >> /etc/php8/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Install PHPMailer
RUN composer install --no-dev --optimize-autoloader

# Simple startup script
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php-fpm8 &' >> /start.sh && \
    echo 'sleep 2' >> /start.sh && \
    echo 'nginx -g "daemon off;"' >> /start.sh && \
    chmod +x /start.sh

# Copy nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Start the application
CMD ["/start.sh"]
