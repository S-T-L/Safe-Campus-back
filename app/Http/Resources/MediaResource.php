<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'libelle' => $this->libelle,
            'description' => $this->description,
            'chemin' => $this->chemin,
            'type' => $this->type->value,
        ];
    }
}
