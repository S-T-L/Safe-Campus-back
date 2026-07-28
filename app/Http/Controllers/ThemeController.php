<?php

namespace App\Http\Controllers;

use App\Http\Resources\ThemeResource;
use App\Models\Theme;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ThemeController extends Controller
{
    /**
     * Alimente l'accueil : sections par theme, cartes par sous-theme.
     */
    public function index(): AnonymousResourceCollection
    {
        $themes = Theme::with('sousThemes')->get();

        return ThemeResource::collection($themes);
    }
}
