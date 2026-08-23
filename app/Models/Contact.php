<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref',
        'nom',
        'prenom',
        'mail',
        'localisation',
        'site_web',
        'horaires',
        'remarques',
        'gratuit',
        'anonyme',
        'actif',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'gratuit' => 'boolean',
            'anonyme' => 'boolean',
            'actif' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * @return HasMany<Telephone, $this>
     */
    public function telephones(): HasMany
    {
        return $this->hasMany(Telephone::class);
    }

    /**
     * @return BelongsToMany<SousTheme, $this>
     */
    public function sousThemes(): BelongsToMany
    {
        return $this->belongsToMany(SousTheme::class)->withPivot('ordre');
    }

    /**
     * Contacts publiables. Un contact inactif reste en base mais disparait du front.
     */
    public function scopeActif(Builder $query): void
    {
        $query->where('actif', true);
    }
}
