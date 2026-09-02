<?php

namespace App\Enums;

enum Province: string
{
    case Sud = 'p-sud';
    case Nord = 'p-nord';
    case Iles = 'p-iles';
    case Toutes = 'all';

    public function libelle(): string
    {
        return match ($this) {
            self::Sud => 'Province Sud',
            self::Nord => 'Province Nord',
            self::Iles => 'Province des Iles Loyaute',
            self::Toutes => 'Tout le territoire',
        };
    }
}
