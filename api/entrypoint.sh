#!/bin/bash
set -e

echo "⏳ Attente de la base de données..."
for i in {1..60}; do
  if (echo > /dev/tcp/db/3306) >/dev/null 2>&1; then
    echo "✅ Base de données accessible"
    break
  fi
  sleep 1
done

if ! (echo > /dev/tcp/db/3306) >/dev/null 2>&1; then
  echo "❌ Impossible de se connecter à la base de données"
  exit 1
fi

echo "📦 Installation des dépendances Composer..."
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
  composer install --no-interaction --optimize-autoloader
else
  echo "✅ Dépendances déjà installées"
fi

echo "🗄️ Création de la base de données..."
php bin/console doctrine:database:create --if-not-exists 2>/dev/null || true

echo "🗑️ Nettoyage du cache..."
rm -rf var/cache/* 2>/dev/null || true

echo "🚀 Démarrage d'Apache en arrière-plan..."
apache2-foreground &
APACHE_PID=$!


