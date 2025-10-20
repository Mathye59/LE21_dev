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

echo "📥 Restauration du backup SQL en arrière-plan..."
BACKUP_FILE="/backup/le21_backup_20251019_204830.sql"
if [ -f "$BACKUP_FILE" ]; then
  (
    echo "   Fichier trouvé: $(ls -lh $BACKUP_FILE | awk '{print $5}')"
    echo "   Attente MySQL (connexion client) - max 15 min..."
    
    # Attendre que MySQL accepte les connexions client (15 min max = 900s)
    CONNECTED=0
    for i in {1..900}; do
      if mysql -h db -u root -proot --skip-ssl -e "SELECT 1" le_21 >/dev/null 2>&1; then
        echo "   ✅ Connexion MySQL OK (après ${i}s)"
        CONNECTED=1
        break
      fi
      if [ $((i % 30)) -eq 0 ]; then
        MIN=$((i / 60))
        SEC=$((i % 60))
        echo "   ⏳ Toujours en attente... (${MIN}min ${SEC}s / 15min)"
      fi
      sleep 1
    done
    
    if [ $CONNECTED -eq 1 ]; then
      echo "   Import en cours..."
      if mysql -h db -u root -proot --skip-ssl --default-character-set=utf8mb4 le_21 < "$BACKUP_FILE" 2>&1 | grep -v "ERROR 1050"; then
        echo "   ✅ Backup restauré"
      else
        echo "   ⚠️  Import échoué"
      fi
    else
      echo "   ⚠️  Timeout MySQL après 15min"
    fi
  ) &
else
  echo "   ⚠️  Aucun backup trouvé dans /backup"
fi

# Attendre Apache (processus principal)
wait $APACHE_PID