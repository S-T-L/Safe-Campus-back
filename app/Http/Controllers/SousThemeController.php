<?php

namespace App\Http\Controllers;

use App\Http\Resources\SousThemeResource;
use App\Models\SousTheme;

class SousThemeController extends Controller
{
    public function show(SousTheme $sousTheme): SousThemeResource
    {
        $sousTheme->load([
            'theme:id,ref,libelle_court',
            'contacts' => fn ($query) => $query->actif(),
            'contacts.telephones',
            'documents',
        ]);

        return new SousThemeResource($sousTheme);
    }
}
