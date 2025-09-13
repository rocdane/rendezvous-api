#!/bin/sh

# Attendre que la base de données soit prête
echo "Waiting for database..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "Database is ready!"

# Générer la clé d'application si nécessaire
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Exécuter les migrations
echo "Running migrations..."
php artisan migrate --force

# Nettoyer et optimiser les caches
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Créer les liens symboliques pour le storage
php artisan storage:link

# Démarrer supervisord
echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
