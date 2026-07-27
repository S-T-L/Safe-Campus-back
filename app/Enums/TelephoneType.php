<?php

namespace App\Enums;

enum TelephoneType: string
{
    case Mobile = 'mobile';
    case Fixe = 'fixe';
    case Sms = 'sms';
    case Urgence = 'urgence';

    public function libelle(): string
    {
        return match ($this) {
            self::Mobile => 'Mobile',
            self::Fixe => 'Fixe',
            self::Sms => 'SMS',
            self::Urgence => 'Urgence',
        };
    }
}
