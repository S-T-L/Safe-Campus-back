<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sert /contact/{theme}/{slug} et /ressources/{theme}/{slug} : meme hero,
 * un seul endpoint.
 */
class SousThemeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ref' => $this->ref,
            'libelle' => $this->libelle,
            'article' => $this->article,
            'intro_ressources' => $this->intro_ressources,
            'ordre' => $this->ordre,
            'theme' => new ThemeResource($this->whenLoaded('theme')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'documents' => MediaResource::collection($this->whenLoaded('documents')),
        ];
    }
}
