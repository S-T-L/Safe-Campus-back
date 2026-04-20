# Guide développement back-end

Stack : **Laravel 12** · **PHP 8.4** · **Inertia v3** · **PostgreSQL 17**

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/        Un contrôleur par ressource (PascalCase)
│   ├── Middleware/         HandleInertiaRequests (Inertia), custom…
│   └── Requests/           Form Requests pour la validation
├── Models/                 Un modèle par table (Eloquent)
├── Services/               Logique métier extraite des contrôleurs
└── Policies/               Autorisation par ressource

database/
├── migrations/             Une migration par changement de schéma
├── seeders/                Données de test
└── factories/              Factories pour les tests

routes/
├── web.php                 Routes Inertia (retournent des pages Vue)
└── console.php             Commandes Artisan custom
```

---

## Conventions

### Contrôleurs

```php
// Un contrôleur = une ressource, 7 méthodes max (CRUD)
// Nommage : NomRessourceController

class HistoireController extends Controller
{
    public function index()   { }  // liste
    public function create()  { }  // formulaire création
    public function store()   { }  // sauvegarde création
    public function show()    { }  // détail
    public function edit()    { }  // formulaire édition
    public function update()  { }  // sauvegarde édition
    public function destroy() { }  // suppression
}
```

### Retour Inertia

```php
use Inertia\Inertia;

// Retourner une page Vue avec des props
return Inertia::render('Histoire/Index', [
    'histoires' => HistoireResource::collection($histoires),
]);

// Redirect après action (POST/PATCH/DELETE)
return redirect()->route('histoires.index');
```

### Modèles Eloquent

```php
class Histoire extends Model
{
    // Toujours définir $fillable ou $guarded
    protected $fillable = ['titre', 'description', 'etat'];

    // Casts pour les types complexes
    protected $casts = [
        'etat'       => EtatHistoire::class,  // Enum PHP 8.1+
        'meta'       => 'array',
    ];

    // Relations nommées explicitement
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class);
    }
}
```

### Validation

```php
// Toujours via Form Request, jamais inline dans le contrôleur
class StoreHistoireRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'titre'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'etat'        => ['required', Rule::enum(EtatHistoire::class)],
        ];
    }
}
```

---

## Base de données

### Migrations

```php
// Une migration = un changement atomique
// Nommage : create_xxx_table / add_xxx_to_xxx_table

Schema::create('scenes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('histoire_fk')->constrained('histoires')->cascadeOnDelete();
    $table->text('dialogue_text');
    $table->timestamps();
});
```

### Requêtes récursives (CTE PostgreSQL)

```php
// Pour parcourir le graphe Scene → Choix → Scene
$parcours = DB::select("
    WITH RECURSIVE parcours AS (
        SELECT scene_id, dialogue_text, 0 AS profondeur
        FROM scenes
        WHERE histoire_fk = ?

        UNION ALL

        SELECT s.scene_id, s.dialogue_text, p.profondeur + 1
        FROM scenes s
        JOIN choix c ON c.fk_next_scene = s.scene_id
        JOIN parcours p ON p.scene_id = c.fk_scene
    )
    SELECT * FROM parcours
", [$histoireId]);
```

### JSONB (PostgreSQL)

```php
// Utiliser le cast 'array' pour les colonnes JSONB
protected $casts = [
    'choice_sequence' => 'array',
];

// Requête sur JSONB en Eloquent
Scene::whereJsonContains('meta->tags', 'intro')->get();
```

---

## Routes

```php
// web.php — routes Inertia uniquement
Route::middleware(['auth'])->group(function () {
    Route::resource('histoires', HistoireController::class);
    Route::resource('histoires.scenes', SceneController::class);
});

// Nommage automatique par resource :
// histoires.index / histoires.show / histoires.store …
```

---

## Commandes utiles (dans le devcontainer)

```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Modèles / contrôleurs
php artisan make:model Scene -mfsc    # model + migration + factory + seeder + controller
php artisan make:request StoreSceneRequest

# Sail (depuis l'hôte)
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
./vendor/bin/sail artisan migrate
```
