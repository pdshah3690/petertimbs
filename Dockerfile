FROM php:7.4-apache

COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY . /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install system packages and PHP extensions needed for WordPress
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip unzip libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd zip

# Enable Apache mod_rewrite for pretty permalinks
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html
