<?php

namespace App\Models;

use App\Enums\TelephoneType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Telephone extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'numero_vert',
        'type',
        'libelle',
        'contact_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TelephoneType::class,
            'numero_vert' => 'boolean',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
