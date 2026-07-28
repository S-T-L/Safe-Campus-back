<?php

namespace App\Http\Controllers;

use App\Http\Resources\SousThemeResource;
use App\Models\SousTheme;

class SousThemeController extends Controller
{
    /**
     * Page /ressources/{theme}/{id} du front : la fiche, ses contacts actifs
     * et leurs telephones.
     *
     * ContactResource::collection() ne va pas chercher les telephones tout
     * seul : sans le eager-load explicite ci-dessous, `telephones` sortirait
     * vide (whenLoaded) ou declencherait un lazy-load requete par requete.
     */
    public function show(SousTheme $sousTheme): SousThemeResource
    {
        $sousTheme->load([
            'contacts' => fn ($query) => $query->actif(),
            'contacts.telephones',
        ]);

        return new SousThemeResource($sousTheme);
    }
}
