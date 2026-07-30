<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref',
        'libelle',
        'resume',
    ];

    public function sousThemes(): HasMany
    {
        return $this->hasMany(SousTheme::class);
    }

    /**
     * Presentation de la page d'accueil : texte, image, video ou logo, non
     * exclusifs entre eux, comme SousTheme::medias().
     *
     * @return BelongsToMany<Media, $this>
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class);
    }
}
