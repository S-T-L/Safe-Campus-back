#!/bin/bash
set -e

composer install --no-interaction
npm install
chmod +x node_modules/.bin/*
npm run build
php artisan key:generate
php artisan filament:install --panels --no-interaction
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate --force