# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

Laravel 13 · PHP 8.4 · Filament 3 · PostgreSQL 17

> API/admin backend uniquement. L'UI applicative est [Safe-Campus-front](../Safe-Campus-front) (Nuxt 3 standalone, repo séparé). `routes/api.php` n'existe pas encore — à créer avant toute intégration réelle avec le front.

👉 Voir [README](README.md) pour l'installation, le devcontainer et les limites de ressources.

## Commands

```bash
# Development (runs Laravel + queue + logs concurrently)
composer dev

# Or individually
php artisan serve

# Testing
composer test          # clears config cache then runs PHPUnit
php artisan test --filter=TestName   # single test

# Database
php artisan migrate
php artisan migrate:fresh --seed

# Generators
php artisan make:model Scene -mfsc   # model + migration + factory + seeder + controller
php artisan make:request StoreSceneRequest

# Docker (alternative)
docker compose up -d
# Then open VS Code Dev Container
```

## Architecture

API/admin backend : les contrôleurs renvoient du JSON, pas de vues. L'admin est géré par Filament (autonome, ne dépend d'aucun asset de ce repo).

```
app/
├── Enums/              Backed enums castés sur les modèles
├── Http/
│   ├── Controllers/    One controller per resource (PascalCase), max 7 CRUD methods
│   └── Requests/       Form Requests for all validation (never inline in controllers)
├── Models/             Eloquent — always define $fillable; use $casts for enums/arrays
├── Services/           Business logic extracted from controllers
├── Policies/           Authorization per resource
└── Providers/Filament/ AdminPanelProvider — config du panel Filament

routes/
├── web.php             Actuellement minimal (health check `/`)
└── api.php             N'existe pas encore — à créer pour servir le front Nuxt
```

Le schéma de base de données est décrit dans [docs/schema_bd.md](docs/schema_bd.md).

## Backend Conventions

**Controllers** return JSON:
```php
return HistoireResource::collection($histoires);
return response()->json(['message' => 'created'], 201);
```

**Routes** use resource routing with auth middleware:
```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('histoires', HistoireController::class);
    Route::apiResource('histoires.scenes', SceneController::class);  // nested
});
```

**PostgreSQL-specific patterns:**
- Use `DB::select()` with raw CTEs for recursive scene→choice→scene graph traversal
- Cast JSONB columns as `'array'`; query with `whereJsonContains()`
- PostgreSQL n'indexe pas la colonne référençante d'une FK : déclarer `->index()` explicitement

## Git

- Workflow git flow : `feature/*` pour le dev, merge sur `dev` en `--no-ff`. `hotfix/*` créé depuis `main`.
- Jamais de `Co-Authored-By` dans les messages de commit.
- Format de message de commit obligatoire :

```
type(scope): phrase résumé.
- ajout 1
- ajout 2
```

Exemple :

```
infra(docker): mise a jour de dépendances sur le conteneur.
- ajout de git
- installation des dépendances php laravel
```
