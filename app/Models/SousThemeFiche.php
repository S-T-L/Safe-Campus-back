<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SousThemeFiche extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'article',
        'sous_theme_id',
    ];

    public function sousTheme(): BelongsTo
    {
        return $this->belongsTo(SousTheme::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'fiche_contact', 'sous_theme_fiche_id', 'contact_id');
    }

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'fiche_media', 'sous_theme_fiche_id', 'media_id');
    }
}
