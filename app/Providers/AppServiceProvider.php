<?php

namespace App\Providers;

use App\Filament\Resources\ContactResource\Pages\ListContacts;
use App\Filament\Resources\ContactResource\RelationManagers\TelephonesRelationManager;
use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Filament\Resources\ThemeResource\Pages\ListThemes;
use App\Filament\Resources\ThemeResource\RelationManagers\SousThemesRelationManager;
use App\Livewire\ThemeSousThemesTable;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerClickToEditHints();
    }

    /**
     * Standard applique a tous les tableaux ou une ligne ouvre la modification
     * au clic : le rappeler en toutes lettres dans l'en-tete du tableau.
     */
    private function registerClickToEditHints(): void
    {
        $hints = [
            'thème' => [ListThemes::class],
            'sous-thème' => [SousThemesRelationManager::class, ThemeSousThemesTable::class],
            'contact' => [ListContacts::class],
            'média' => [ListMedia::class],
            'téléphone' => [TelephonesRelationManager::class],
        ];

        foreach ($hints as $label => $scopes) {
            FilamentView::registerRenderHook(
                TablesRenderHook::TOOLBAR_START,
                fn () => Blade::render(
                    '<span class="fi-ta-hint text-sm italic text-gray-500 dark:text-gray-400">Pour modifier un '.e($label).', cliquez dessus.</span>'
                ),
                scopes: $scopes,
            );
        }
    }
}
