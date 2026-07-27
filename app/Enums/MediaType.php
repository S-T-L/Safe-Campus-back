<?php

namespace App\Enums;

enum MediaType: string
{
    case Image = 'image';
    case Video = 'video';
    case Audio = 'audio';
    case Document = 'document';

    public function libelle(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Video => 'Vidéo',
            self::Audio => 'Audio',
            self::Document => 'Document',
        };
    }
}
