FROM php:8.2-apache

# Install required packages
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-install zip pdo pdo_mysql mysqli

# Enable Apache modules
RUN a2enmod rewrite headers

# Configure Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier uniquement composer.json et lock pour installer les deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copier le reste du projet
COPY . .

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/public
