FROM php:8.2-apache

# Installation des dépendances OS (intl + composer requis)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev git unzip zlib1g-dev \
 && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP
RUN docker-php-ext-install pdo pdo_mysql \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl

# Configuration Apache: rewrite + docroot /public
RUN a2enmod rewrite

# CONFIGURATION APACHE CORRIGÉE
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    \n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Installation de Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Limites d'upload PHP
RUN { \
  echo 'file_uploads=On'; \
  echo 'upload_max_filesize=50M'; \
  echo 'post_max_size=500M'; \
  echo 'max_file_uploads=10'; \
  echo 'memory_limit=512M'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Limite Apache pour les uploads
RUN printf '\nLimitRequestBody 262144000\n' >> /etc/apache2/apache2.conf

# Copie du script d'entrée
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Définition du répertoire de travail
WORKDIR /var/www/html

# Démarrage via le script d'entrée
CMD ["/entrypoint.sh"]