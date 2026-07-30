<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    /**
     * L'inflecteur Laravel rend « media » (deja pluriel en anglais). La table
     * est nommee « medias », il faut donc la declarer explicitement.
     */
    protected $table = 'medias';

    protected $fillable = [
        'libelle',
        'chemin',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
        ];
    }

    /**
     * @return BelongsToMany<SousTheme, $this>
     */
    public function sousThemes(): BelongsToMany
    {
        return $this->belongsToMany(SousTheme::class);
    }

    /**
     * @return BelongsToMany<Theme, $this>
     */
    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class);
    }
}
