# Washington Airfreight Landing Page Docker Image
# Single city deployment for washington.emexexpress.de with IONOS SMTP

FROM nginx:alpine

# Install PHP and required extensions for email functionality
RUN apk add --no-cache \
    php8 \
    php8-fpm \
    php8-mbstring \
    php8-xml \
    php8-curl \
    php8-json \
    composer \
    && rm -rf /var/cache/apk/*

# Configure PHP-FPM
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/' /etc/php8/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Copy Chicago landing page files
COPY . /var/www/html/

# Install PHPMailer
RUN composer install --no-dev --optimize-autoloader

# Copy custom configurations
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY php.ini /etc/php8/php.ini

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
ENV CITY_NAME="Washington"

# Expose port
EXPOSE 80

# Start script
CMD ["/start.sh"]
