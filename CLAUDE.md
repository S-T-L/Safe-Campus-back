# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

Laravel 12 · PHP 8.4 · Inertia v3 · Vue 3 · Vite 7 · PostgreSQL 17

## Commands

```bash
# Development (runs Laravel + Vite + queue + logs concurrently)
composer dev

# Or individually
php artisan serve
npm run dev

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

This is a server-rendered SPA using **Inertia.js**: controllers return Vue pages via `Inertia::render()`, not JSON. No separate API needed for page data.

```
app/
├── Http/
│   ├── Controllers/    One controller per resource (PascalCase), max 7 CRUD methods
│   ├── Middleware/     HandleInertiaRequests shares props to all Vue pages
│   └── Requests/       Form Requests for all validation (never inline in controllers)
├── Models/             Eloquent — always define $fillable; use $casts for enums/arrays
├── Services/           Business logic extracted from controllers
└── Policies/           Authorization per resource

routes/
└── web.php             Inertia routes only (return Vue pages, not JSON)

resources/js/
├── Pages/              One .vue file per route (matched by Inertia::render name)
├── Components/         Reusable components; generic ones prefixed App
├── Layouts/
└── Composables/        useXxx.js pattern

resources/css/
├── app.css             Entry point
└── main.css            All styles — CSS variables + BEM components
```

## Backend Conventions

**Controllers** return Inertia responses or redirects:
```php
return Inertia::render('Histoire/Index', ['histoires' => HistoireResource::collection($histoires)]);
return redirect()->route('histoires.index');  // after POST/PATCH/DELETE
```

**Routes** use resource routing with auth middleware:
```php
Route::middleware(['auth'])->group(function () {
    Route::resource('histoires', HistoireController::class);
    Route::resource('histoires.scenes', SceneController::class);  // nested
});
```

**PostgreSQL-specific patterns:**
- Use `DB::select()` with raw CTEs for recursive scene→choice→scene graph traversal
- Cast JSONB columns as `'array'`; query with `whereJsonContains()`

## Frontend Conventions

**No `<style>` blocks in `.vue` files** — all CSS goes in `resources/css/main.css`.

**BEM naming** everywhere: `.bloc__element--modificateur`

**CSS variables** for all shared values — see `resources/css/main.css` for the full list (colors, spacing, radius, shadows prefixed `--color-`, `--spacing-`, `--radius-`, `--shadow-`).

**Component structure order:** imports → props & emits → local state (`ref`/`reactive`) → computed → functions.

**Data flow:** controllers pass data as Inertia props — no `fetch()`/`axios` for page data. Use axios only for POST/PATCH/DELETE actions.

**Inertia navigation:**
```vue
import { Link, router } from '@inertiajs/vue3'
// Declarative: <Link href="/histoires">
// Programmatic: router.visit('/histoires')
// Prop refresh: router.reload()
```
