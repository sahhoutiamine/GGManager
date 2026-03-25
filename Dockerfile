FROM php:8.4-apache

# Set UID to match host user to avoid workspace permissions issues
ARG uid=1000
RUN usermod -u $uid www-data && groupmod -g $uid www-data

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Clear apt cache to optimize image layer footprint
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions for Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite for Laravel routes (crucial for clean URLs)
RUN a2enmod rewrite

# Configure Apache DocumentRoot to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Get the latest Composer executable
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache is set to run by default on its master 'root' thread, and spawn child
# processes automatically using the 'www-data' mapped user. 
