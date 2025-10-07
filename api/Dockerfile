FROM php:8.2-apache

# OS deps (intl + composer requis)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev git unzip zlib1g-dev \
 && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl

# Apache: rewrite + docroot /public
RUN a2enmod rewrite
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && printf '\n<Directory /var/www/html/public>\n  AllowOverride All\n  Require all granted\n  FallbackResource /index.php\n</Directory>\n' >> /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Installer Composer dans l'image
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
RUN printf '\nLimitRequestBody 262144000\n' >> /etc/apache2/apache2.conf

# CMD : install bundles si nécessaire, puis wait DB, migrations, cache, Apache
CMD bash -lc '\
  if [ ! -f composer.json ]; then echo "⚠️ composer.json introuvable (le volume est-il monté ?)"; fi; \
  if [ ! -d vendor ] || [ composer.lock -nt vendor/autoload.php ]; then \
    echo "▶ composer install"; \
    composer install --no-interaction --prefer-dist --optimize-autoloader; \
  else \
    echo "✔ vendor up-to-date"; \
  fi; \
  echo "⏳ Attente DB db:3306..."; \
  for i in {1..60}; do (echo > /dev/tcp/db/3306) >/dev/null 2>&1 && break || sleep 1; done; \
  (echo > /dev/tcp/db/3306) >/dev/null 2>&1 || { echo "❌ DB KO"; exit 1; }; \
  php bin/console doctrine:database:create --if-not-exists || true; \
  php bin/console doctrine:migrations:migrate -n || { echo "❌ migrations KO"; exit 1; }; \
  php bin/console cache:clear || true; \
  exec apache2-foreground \
'
