FROM php:8.2-apache

# Install PHP extensions required by AircraftScheduling
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set recommended PHP settings
RUN { \
        echo 'error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED'; \
        echo 'display_errors = On'; \
        echo 'session.save_path = /tmp'; \
        echo 'upload_max_filesize = 10M'; \
        echo 'post_max_size = 10M'; \
    } > /usr/local/etc/php/conf.d/aircraftscheduling.ini

# Copy application files
COPY Web/ /var/www/html/
COPY img/ /var/www/img/

# Copy Docker-specific SiteSpecific.inc (overrides the default)
COPY docker/SiteSpecific.inc /var/www/html/SiteSpecific.inc

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www/html /var/www/img

EXPOSE 80
