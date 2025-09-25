FROM php:8.2-apache

# Install required packages and PHP extensions
RUN apt-get update \
    && apt-get install -y libzip-dev zip git unzip libonig-dev \
    && docker-php-ext-install zip mbstring pdo pdo_mysql mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Copy all application files
COPY . .

# Set proper permissions

# Set Apache document root to /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/public
