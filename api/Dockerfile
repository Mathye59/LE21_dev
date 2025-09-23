FROM php:8.3-apache

# packages + extensions PHP nécessaires à Symfony + MySQL
RUN apt-get update \
 && apt-get install -y git unzip libicu-dev libzip-dev mariadb-client \
 && docker-php-ext-install intl pdo pdo_mysql opcache zip

# Apache → servir /public
RUN a2enmod rewrite headers
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}>!g' /etc/apache2/apache2.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Installe les vendors (sans exécuter de scripts pendant le build)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts -vvv

# Puis copie le reste du code
COPY . ./
RUN composer dump-autoload --classmap-authoritative

# Warmup (si APP_SECRET présent) + permissions var/
ENV APP_ENV=prod
RUN php bin/console cache:warmup || true \
 && chown -R www-data:www-data var

# Entrypoint : attend MySQL + migrations auto → lance Apache
COPY docker-run.sh /usr/local/bin/docker-run.sh
RUN chmod +x /usr/local/bin/docker-run.sh
ENTRYPOINT ["/usr/local/bin/docker-run.sh"]
