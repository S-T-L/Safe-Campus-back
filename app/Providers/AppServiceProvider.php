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
use Filament\View\PanelsRenderHook;
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
        $this->registerAdminBorderStyles();
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

    private function registerAdminBorderStyles(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn () => Blade::render(<<<'HTML'
                <style>
                    .ring-gray-950\/5 {
                        --tw-ring-color: rgb(66 96 230 / 0.45) !important;
                    }

                    .ring-gray-950\/10 {
                        --tw-ring-color: rgb(66 96 230 / 0.6) !important;
                    }

                    .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
                        border: 1px solid rgb(66 96 230 / 0.6);
                    }

                    .fi-section-header .fi-icon-btn {
                        color: rgb(66 96 230) !important;
                    }

                    .fi-section-header .fi-icon-btn:hover {
                        color: rgb(59 86 207) !important;
                    }

                    .fi-section-header .fi-icon-btn-icon {
                        width: 1.75rem !important;
                        height: 1.75rem !important;
                    }
                </style>
                HTML
            ),
        );
    }
}
