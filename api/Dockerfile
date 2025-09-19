# Image runtime web + PHP (Apache)
FROM php:8.3-apache

# Packages système + extensions PHP (MySQL)
RUN apt-get update \
 && apt-get install -y git unzip libicu-dev libzip-dev libonig-dev mariadb-client \
 && docker-php-ext-install intl pdo pdo_mysql opcache

# Apache: activer rewrite + pointer sur /public
RUN a2enmod rewrite headers
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}>!g' /etc/apache2/apache2.conf

# Composer dans l'image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Installer les dépendances en prod (meilleur cache Docker)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# Copier tout le code
COPY . ./

# Prod: cache Symfony
ENV APP_ENV=prod
RUN php bin/console cache:clear --no-warmup \
 && php bin/console cache:warmup \
 && chown -R www-data:www-data var