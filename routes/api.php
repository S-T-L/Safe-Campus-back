<?php

use App\Http\Controllers\SousThemeController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

// Annuaire : lecture publique, sans authentification.
Route::get('themes', [ThemeController::class, 'index']);
Route::get('sous-themes/{sousTheme:ref}', [SousThemeController::class, 'show']);
