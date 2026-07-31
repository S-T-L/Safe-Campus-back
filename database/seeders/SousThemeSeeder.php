<?php

namespace Database\Seeders;

use App\Models\SousTheme;
use App\Models\Theme;
use Illuminate\Database\Seeder;

/**
 * Taxonomie minimale (ref + libelle), revue avec l'equipe le 2026-07-31 :
 * 3 themes x 3 sous-themes. Diverge de docs/schema_bd.md (taxonomie,
 * theme transverse, `jeux_ecrans`, `crise_suicidaire`,
 * `detresse_psychologique`, `violences_intrafamiliales`) — voir
 * database/seeders/data/NOTES.md.
 */
class SousThemeSeeder extends Seeder
{
    /**
     * @var list<array{ref: string, libelle: string, theme_ref: string}>
     */
    private const SOUS_THEMES = [
        ['ref' => 'alcool', 'libelle' => 'Alcool', 'theme_ref' => 'addictions'],
        ['ref' => 'tabac', 'libelle' => 'Tabac', 'theme_ref' => 'addictions'],
        ['ref' => 'drogue', 'libelle' => 'Drogue', 'theme_ref' => 'addictions'],
        ['ref' => 'anxiete', 'libelle' => 'Anxiété', 'theme_ref' => 'sante_mentale'],
        ['ref' => 'depression', 'libelle' => 'Dépression', 'theme_ref' => 'sante_mentale'],
        ['ref' => 'burn_out', 'libelle' => 'Burn-out', 'theme_ref' => 'sante_mentale'],
        ['ref' => 'violences_sexistes', 'libelle' => 'Violences sexistes', 'theme_ref' => 'vss'],
        ['ref' => 'violences_sexuelles', 'libelle' => 'Violences sexuelles', 'theme_ref' => 'vss'],
        ['ref' => 'harcelement', 'libelle' => 'Harcèlement', 'theme_ref' => 'vss'],
    ];

    public function run(): void
    {
        $themeIds = Theme::pluck('id', 'ref');

        foreach (self::SOUS_THEMES as $sousTheme) {
            SousTheme::updateOrCreate(
                ['ref' => $sousTheme['ref']],
                [
                    'libelle' => $sousTheme['libelle'],
                    'theme_id' => $themeIds[$sousTheme['theme_ref']],
                ]
            );
        }
    }
}
