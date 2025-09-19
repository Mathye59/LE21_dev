FROM php:8.3-cli-alpine

# dépendances & extensions nécessaires à Symfony + MySQL
RUN apk add --no-cache icu-dev oniguruma-dev libzip-dev git unzip bash \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql

# Composer dans le conteneur (pratique pour installer des bundles)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Lance le serveur PHP intégré sur le port 8080 en servant /public
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]