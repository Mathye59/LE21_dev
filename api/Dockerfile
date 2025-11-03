FROM php:8.2-apache

# OS deps + mysql client + netcat pour l'entrypoint
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev git unzip zlib1g-dev default-mysql-client netcat-openbsd \
 && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl

# Apache
RUN a2enmod rewrite && \
    cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:80>
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# PHP upload limits augmentation
RUN { \
  echo 'file_uploads=On'; \
  echo 'upload_max_filesize=50M'; \
  echo 'post_max_size=500M'; \
  echo 'max_file_uploads=10'; \
  echo 'memory_limit=512M'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Apache upload limit
RUN printf '\nLimitRequestBody 262144000\n' >> /etc/apache2/apache2.conf

# ---- entrypoint.sh ----
COPY entrypoint.sh /entrypoint.sh
# rendre exécutable + enlever CRLF + enlever le BOM
RUN chmod +x /entrypoint.sh \
 && sed -i 's/\r$//' /entrypoint.sh \
 && sed -i '1s/^\xEF\xBB\xBF//' /entrypoint.sh


WORKDIR /var/www/html

# On garde l'entrypoint officiel PHP
ENTRYPOINT ["docker-php-entrypoint"]
# On appelle explicitement sh -> plus besoin de bash ni de shebang
CMD ["/bin/sh", "/entrypoint.sh"]