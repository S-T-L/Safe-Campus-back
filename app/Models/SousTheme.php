<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Le sous-theme est la fiche : « libelle » est le titre affiche, « article »
 * le contenu editorial. Il n'existe pas de table Fiche separee.
 */
class SousTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref',
        'libelle',
        'resume',
        'article',
        'intro_ressources',
        'theme_id',
        'permet_signalement',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'permet_signalement' => 'boolean',
        ];
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function signalements(): HasMany
    {
        return $this->hasMany(Signalement::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)
            ->withPivot('ordre')
            ->orderByPivot('ordre');
    }

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)
            ->withPivot('ordre')
            ->orderByPivot('ordre');
    }

    /**
     * @return BelongsToMany<Media, $this>
     */
    public function documents(): BelongsToMany
    {
        return $this->medias()->where('type', MediaType::Document);
    }
}
