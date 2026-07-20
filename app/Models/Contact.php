<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'mail',
        'localisation',
    ];

    public function telephones(): HasMany
    {
        return $this->hasMany(Telephone::class);
    }

    public function fiches(): BelongsToMany
    {
        return $this->belongsToMany(SousThemeFiche::class, 'fiche_contact', 'contact_id', 'sous_theme_fiche_id');
    }
}
