#!/usr/bin/env sh
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

echo "🔍 === DEBUG RÉSEAU ==="
echo "DB_HOST: ${DB_HOST}"
echo "DB_PORT: ${DB_PORT}"
echo "DATABASE_URL: ${DATABASE_URL}"
echo ""

echo "🔍 Test DNS..."
nslookup ${DB_HOST} || echo "⚠️  DNS échoué"
echo ""

echo "🔍 Test Ping..."
ping -c 2 ${DB_HOST} || echo "⚠️  Ping échoué"
echo ""

echo "🔍 Vérification nc installé..."
which nc || echo "❌ NC NON INSTALLÉ!"
nc -h 2>&1 | head -5
echo ""

echo "⏳ Attente de la base de données (${DB_HOST}:${DB_PORT})..."

i=0
max_wait=120

while ! nc -zv "$DB_HOST" "$DB_PORT" 2>&1; do
  i=$((i+1))
  if [ "$i" -ge "$max_wait" ]; then
    echo "❌ Timeout après ${max_wait}s"
    echo "🔍 Dernière tentative avec telnet..."
    telnet "$DB_HOST" "$DB_PORT" || true
    exit 1
  fi
  
  if [ $((i % 10)) -eq 0 ]; then
    echo "   ⏱️  Tentative ${i}/${max_wait}..."
  fi
  
  sleep 1
done

echo "✅ Base de données accessible!"

echo ""
echo "📦 Installation Composer..."
if [ ! -f "vendor/autoload.php" ]; then
  composer install --no-interaction --optimize-autoloader
else
  echo "✅ Dépendances déjà installées"
fi

echo ""
echo "🗄️  Création DB..."
php bin/console doctrine:database:create --if-not-exists 2>/dev/null || true

echo ""
echo "🗑️  Cache clear..."
rm -rf var/cache/* 2>/dev/null || true

echo ""
echo "🚀 Démarrage Apache..."
exec apache2-foreground