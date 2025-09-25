
FROM php:8.2-apache

# Activer les modules Apache
RUN a2enmod rewrite

# Installer les extensions PHP nécessaires
RUN apt-get update \
	&& apt-get install -y unzip git libonig-dev \
	&& docker-php-ext-install mbstring \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Installer les dépendances PHP avec Composer
RUN composer install || true
RUN composer dump-autoload --optimize
