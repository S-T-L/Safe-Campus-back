<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SousTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'theme_id',
        'permet_signalement',
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

    public function fiches(): HasMany
    {
        return $this->hasMany(SousThemeFiche::class);
    }

    public function signalements(): HasMany
    {
        return $this->hasMany(Signalement::class);
    }
}
