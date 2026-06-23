# CLAUDE.md

**Stack:** Laravel 12 · PHP 8.4 · Inertia v3 · Vue 3 · Vite 7 · PostgreSQL 17

👉 See [README](README.md) for setup, commands, devcontainer workflow, and resource limits.

---

## Architecture

Server-rendered SPA using **Inertia.js**: controllers return Vue pages via `Inertia::render()`, not JSON.

```
app/
├── Http/Controllers/     One per resource (PascalCase), max 7 CRUD methods
├── Http/Requests/        Form Requests for all validation
├── Models/               Eloquent — always $fillable; use $casts
├── Services/             Business logic
└── Policies/             Authorization

routes/web.php            Inertia routes only

resources/js/Pages/       One .vue per route
resources/js/Components/  Reusable; App* for generic
resources/css/main.css    All styles — CSS vars + BEM
```

---

## Code Conventions

**Controllers** return Inertia or redirect:
```php
return Inertia::render('Scene/Index', ['scenes' => SceneResource::collection(...)]);
return redirect()->route('scenes.index');
```

**Routes** use resource + auth:
```php
Route::middleware(['auth'])->resource('scenes', SceneController::class);
```

**PostgreSQL:** Use raw CTEs for recursive graphs; cast JSONB as `'array'`.

**Frontend:** No `<style>` in `.vue` — all in `main.css`. BEM naming: `.bloc__element--mod`. CSS vars for shared values. No `fetch()` for page data — use Inertia props.
