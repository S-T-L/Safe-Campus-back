<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HistoireController;
use App\Http\Controllers\SousThemeController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

// Authentification visiteur front : identifiants centralises en .env, un
// seul compte partage. Emet un token Sanctum, pas de session/cookie.
Route::post('login', [AuthController::class, 'login']);

// Annuaire : lecture publique, sans authentification.
Route::get('themes', [ThemeController::class, 'index']);
Route::get('sous-themes/{sousTheme:ref}', [SousThemeController::class, 'show']);
Route::get('histoires/{histoire:ref}', [HistoireController::class, 'show']);
