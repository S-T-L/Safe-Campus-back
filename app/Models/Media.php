<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'chemin',
        'type',
    ];

    public function fiches(): BelongsToMany
    {
        return $this->belongsToMany(SousThemeFiche::class, 'fiche_media', 'media_id', 'sous_theme_fiche_id');
    }
}
