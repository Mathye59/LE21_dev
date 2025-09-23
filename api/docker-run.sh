#!/usr/bin/env bash
set -euo pipefail

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-le_21}"
DB_PASSWORD="${DB_PASSWORD:-le_21}"

echo "🔄 Attente MySQL (${DB_HOST}:${DB_PORT})…"
until mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" --silent; do
  sleep 1
done
echo "✅ MySQL OK"

# crée la base si besoin (idempotent)
php bin/console doctrine:database:create --if-not-exists -n || true

# migrations automatiques (idempotent)
php bin/console doctrine:migrations:migrate -n || true

echo "🚀 Apache / Symfony"
exec apache2-foreground
