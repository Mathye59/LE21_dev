#!/usr/bin/env bash
set -e
php -v
composer install --no-interaction || true
php bin/console cache:clear || true
exec apache2-foreground