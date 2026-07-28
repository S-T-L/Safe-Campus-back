<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Version legere d'un sous-theme pour les cartes de navigation (accueil).
 * Ni article, ni contacts : ca reste au SousThemeResource de la page detail.
 */
class SousThemeSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ref' => $this->ref,
            'libelle' => $this->libelle,
        ];
    }
}
