<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Taxonomie minimale (ref + libelle). Resume et medias restent a la charge
 * d'un redacteur via Filament. Voir docs/schema_bd.md#taxonomie.
 */
class ThemeSeeder extends Seeder
{
    /**
     * @var list<array{ref: string, libelle: string}>
     */
    private const THEMES = [
        ['ref' => 'sante_mentale', 'libelle' => 'Santé mentale'],
        ['ref' => 'addictions', 'libelle' => 'Addictions'],
        ['ref' => 'vss', 'libelle' => 'Violence'],
    ];

    public function run(): void
    {
        foreach (self::THEMES as $theme) {
            Theme::updateOrCreate(['ref' => $theme['ref']], $theme);
        }
    }
}
