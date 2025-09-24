FROM php:8.2-apache

# Activer les modules Apache
RUN a2enmod rewrite

# Configurer Apache pour autoriser les fichiers .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Définir le répertoire racine du document
RUN sed -i 's|/var/www/html|/var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Installer extensions PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /var/www/html
