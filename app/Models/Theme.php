<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'libelle_court',
        'resume',
        'ordre',
    ];

    /**
     * @return HasMany<SousTheme, $this>
     */
    public function sousThemes(): HasMany
    {
        return $this->hasMany(SousTheme::class)->orderBy('ordre');
    }

    /**
     * @param  Builder<Theme>  $query
     */
    public function scopeOrdonne(Builder $query): void
    {
        $query->orderBy('ordre')->orderBy('id');
    }

    /**
     * @return BelongsToMany<Media, $this>
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class);
    }
}
