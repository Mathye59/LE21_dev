#!/bin/bash
# Script d'initialisation du conteneur API
# Gère l'attente de la DB, les migrations et le démarrage d'Apache

set -e  # Arrête le script en cas d'erreur

echo "⏳ Attente de la base de données (db:3306)..."

# Boucle d'attente : essaie de se connecter à MySQL pendant 60 secondes
for i in {1..60}; do
  # Tente une connexion TCP au port 3306 de 'db'
  if (echo > /dev/tcp/db/3306) >/dev/null 2>&1; then
    echo "✅ Base de données accessible"
    break
  fi
  sleep 1
done

# Vérifie une dernière fois que la DB est accessible
if ! (echo > /dev/tcp/db/3306) >/dev/null 2>&1; then
  echo "❌ Impossible de se connecter à la base de données"
  exit 1
fi

echo "📦 Création de la base de données si nécessaire..."
php bin/console doctrine:database:create --if-not-exists 2>/dev/null || true

echo "🔄 Exécution des migrations..."
php bin/console doctrine:migrations:migrate -n 2>/dev/null || true

echo "🗑️ Nettoyage du cache..."
rm -rf var/cache/* 2>/dev/null || true

echo "🚀 Démarrage d'Apache..."
# exec remplace le processus actuel par Apache (nécessaire pour Docker)
exec apache2-foreground