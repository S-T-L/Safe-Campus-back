<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * `next_scene_id` null = sortie du parcours, qualifiee par `issue`.
 * `contacts` n'existe que sur un choix defavorable, resolu par le service.
 */
class ChoixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text_choix' => $this->text_choix,
            'next_scene_id' => $this->next_scene_id,
            'issue' => $this->issue?->value,
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
        ];
    }
}
