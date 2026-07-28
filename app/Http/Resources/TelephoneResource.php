<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TelephoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'numero' => $this->numero,
            'numero_vert' => $this->numero_vert,
            'type' => $this->type->value,
            'libelle' => $this->libelle,
        ];
    }
}
