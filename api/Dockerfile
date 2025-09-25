FROM php:8.2-apache

# extensions indispensables pour Doctrine
RUN docker-php-ext-install pdo pdo_mysql

# Apache : activer rewrite et servir /public
RUN a2enmod rewrite
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && printf '\n<Directory /var/www/html/public>\n  AllowOverride All\n  Require all granted\n</Directory>\n' >> /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
