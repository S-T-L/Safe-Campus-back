#!/bin/bash
set -e

# Ensure the container user owns the workspace (UID mismatch between host and container)
sudo chown -R "$(id -u):$(id -g)" . 2>/dev/null || true

COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction

if [ -z "$(grep '^APP_KEY=.\+' .env 2>/dev/null)" ]; then
    php artisan key:generate
fi

# Ne tourne qu'une seule fois — ignoré si les fichiers sont déjà dans le repo
if [ ! -f app/Providers/Filament/AdminPanelProvider.php ]; then
    php artisan filament:install --panels --no-interaction
fi

if [ ! -f config/sanctum.php ]; then
    php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
fi

php artisan vendor:publish --tag=filament-assets --force
php artisan migrate --force

echo ""
echo "✅ Setup terminé — environnement prêt."
