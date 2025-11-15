# Chicago Airfreight Landing Page Docker Image
# Single city deployment for chicago.emexexpress.de with IONOS SMTP

FROM nginx:alpine

# Install PHP and required extensions for email functionality
RUN apk add --no-cache \
    php \
    php-fpm \
    php-mbstring \
    php-xml \
    php-curl \
    php-json \
    php-ctype \
    php-tokenizer \
    php-openssl \
    composer \
    && rm -rf /var/cache/apk/*

# Configure PHP-FPM to use Unix socket
RUN sed -i 's/listen = 127.0.0.1:9000/listen = \/run\/php\/php-fpm.sock/' /etc/php*/php-fpm.d/www.conf && \
    sed -i 's/;listen.owner = nobody/listen.owner = nginx/' /etc/php*/php-fpm.d/www.conf && \
    sed -i 's/;listen.group = nobody/listen.group = nginx/' /etc/php*/php-fpm.d/www.conf && \
    sed -i 's/;listen.mode = 0660/listen.mode = 0660/' /etc/php*/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Copy Chicago landing page files
COPY . /var/www/html/

# Install PHPMailer
RUN composer install --no-dev --optimize-autoloader

# Create log and socket directories for PHP
RUN mkdir -p /var/log/php /run/php && \
    touch /var/log/php/error.log && \
    chown -R nginx:nginx /var/log/php /run/php

# Copy custom configurations
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY php.ini /etc/php/php.ini

# Create startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Set environment variables for IONOS SMTP
ENV SMTP_HOST=${SMTP_HOST:-mail.ionos.de}
ENV SMTP_PORT=${SMTP_PORT:-587}
ENV SMTP_USER=${SMTP_USER}
ENV SMTP_PASS=${SMTP_PASS}
ENV SMTP_ENCRYPTION=${SMTP_ENCRYPTION:-tls}
ENV ADMIN_EMAIL=${ADMIN_EMAIL:-ops@emexexpress.de}
ENV FROM_EMAIL=${FROM_EMAIL:-noreply@emexexpress.de}
ENV CITY_NAME="Chicago"

# Expose port
EXPOSE 80

# Start script
CMD ["/start.sh"]
