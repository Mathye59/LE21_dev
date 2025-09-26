FROM php:8.2-apache

# Extensions nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Apache: rewrite + DocumentRoot /public
RUN a2enmod rewrite
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && printf '\n<Directory /var/www/html/public>\n  AllowOverride All\n  Require all granted\n  FallbackResource /index.php\n</Directory>\n' >> /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# (optionnel) composer si tu veux builder l’image sans bind-mount
# COPY api/composer.* ./
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
#  && composer install --no-interaction --prefer-dist

# CMD sans fichier .sh :
# - attend la DB (bash /dev/tcp)
# - crée la base si besoin
# - exécute les migrations
# - clear cache
# - lance Apache
CMD bash -lc '\
  echo "⏳ Attente DB db:3306..." ; \
  for i in {1..60}; do (echo > /dev/tcp/db/3306) >/dev/null 2>&1 && break || sleep 1; done ; \
  (echo > /dev/tcp/db/3306) >/dev/null 2>&1 || { echo "❌ DB KO"; exit 1; } ; \
  php bin/console doctrine:database:create --if-not-exists || true ; \
  php bin/console doctrine:migrations:migrate -n || { echo "❌ migrations KO"; exit 1; } ; \
  php bin/console cache:clear || true ; \
  exec apache2-foreground \
'
