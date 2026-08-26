<?php

namespace App\Http\Controllers;

use App\Http\Resources\SousThemeResource;
use App\Models\SousTheme;

class SousThemeController extends Controller
{
    public function show(SousTheme $sousTheme): SousThemeResource
    {
        abort_unless($sousTheme->actif, 404);

        $sousTheme->load([
            'theme:id,ref,libelle_court',
            'contacts' => fn ($query) => $query->actif(),
            'contacts.telephones' => fn ($query) => $query->actif(),
            'documents' => fn ($query) => $query->actif(),
        ]);

        return new SousThemeResource($sousTheme);
    }
}
