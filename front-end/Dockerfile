FROM php:8.2-apache

# Activer les modules Apache
RUN a2enmod rewrite

# Installer extensions PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /var/www/html
