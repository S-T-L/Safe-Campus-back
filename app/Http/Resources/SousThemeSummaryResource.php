<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Version legere d'un sous-theme pour les cartes de navigation (accueil).
 * `resume` est le teaser de la carte, distinct d'`article` (contenu detaille
 * de la page sous-theme). Ni article, ni contacts ici : ca reste au
 * SousThemeResource de la page detail.
 */
class SousThemeSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ref' => $this->ref,
            'libelle' => $this->libelle,
            'resume' => $this->resume,
        ];
    }
}
