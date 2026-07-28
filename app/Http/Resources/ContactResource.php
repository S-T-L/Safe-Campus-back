<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * `telephones` doit etre eager-charge par le controller (`->load('contacts.telephones')`).
     * `whenLoaded` evite un lazy-load silencieux si ce n'est pas le cas.
     */
    public function toArray(Request $request): array
    {
        return [
            'ref' => $this->ref,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'mail' => $this->mail,
            'localisation' => $this->localisation,
            'site_web' => $this->site_web,
            'horaires' => $this->horaires,
            'remarques' => $this->remarques,
            'gratuit' => $this->gratuit,
            'anonyme' => $this->anonyme,
            'telephones' => TelephoneResource::collection($this->whenLoaded('telephones')),
        ];
    }
}
