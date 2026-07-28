<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ref' => $this->ref,
            'libelle' => $this->libelle,
            'sous_themes' => SousThemeSummaryResource::collection($this->whenLoaded('sousThemes')),
        ];
    }
}
