<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signalement extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_antispam',
        'sous_theme_id',
        'texte',
    ];

    public function sousTheme(): BelongsTo
    {
        return $this->belongsTo(SousTheme::class);
    }
}
