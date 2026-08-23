<?php

namespace Database\Seeders;

use App\Enums\MediaType;
use App\Models\Media;
use App\Models\SousTheme;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Fiches reflectives de la page /ressources/{theme}/{slug}, reprises du
 * front (app/data/themes.js), voir docs/textes-front.md. Un media par fiche
 * (type Document, chemin vide — aucun PDF n'existe), attache au sous-theme
 * via media_sous_theme avec son ordre d'affichage.
 *
 * Idempotent via `libelle` : Media n'a pas de `ref`, mais son libelle sert
 * deja de cle de recherche (voir docs/schema_bd.md#media).
 */
class MediaSeeder extends Seeder
{
    /**
     * @var array<string, list<array{titre: string, description: string}>>
     */
    private const DOCUMENTS = [
        'alcool' => [
            ['titre' => 'Fiche réflexive — Faire le point sur ma consommation', 'description' => "Un questionnaire simple pour t'aider à évaluer ta relation à l'alcool et identifier les situations à risque."],
            ['titre' => 'Fiche pratique — Réduire sans se priver', 'description' => 'Des stratégies concrètes pour espacer ou diminuer sa consommation au quotidien, étape par étape.'],
        ],
        'tabac' => [
            ['titre' => 'Fiche réflexive — Identifier mes déclencheurs', 'description' => 'Repère les moments et émotions qui te poussent à fumer pour mieux les anticiper.'],
            ['titre' => 'Guide — Les premières semaines sans tabac', 'description' => "Ce à quoi s'attendre physiquement et mentalement, et comment tenir bon."],
        ],
        'drogue' => [
            ['titre' => 'Fiche réflexive — Mon rapport aux substances', 'description' => "Un outil d'auto-évaluation pour mieux cerner ta consommation et ses effets sur ton quotidien."],
            ['titre' => 'Fiche info — Réduction des risques', 'description' => 'Des conseils concrets pour limiter les dangers en cas de consommation.'],
        ],
        'anxiete' => [
            ['titre' => 'Fiche réflexive — Cartographier mon anxiété', 'description' => "Identifie les situations, pensées et sensations liées à tes moments d'anxiété."],
            ['titre' => "Fiche pratique — Exercices de respiration et d'ancrage", 'description' => 'Des techniques rapides à utiliser en cas de montée de stress.'],
        ],
        'depression' => [
            ['titre' => 'Fiche réflexive — Reconnaître les signaux', 'description' => "Une liste de symptômes courants pour t'aider à mettre des mots sur ce que tu ressens."],
            ['titre' => 'Fiche pratique — Petits pas au quotidien', 'description' => 'Des actions simples et réalistes à mettre en place quand tout paraît difficile.'],
        ],
        'burn_out' => [
            ['titre' => 'Fiche réflexive — Évaluer ma charge mentale', 'description' => 'Un outil pour visualiser tes sources de surcharge et de fatigue.'],
            ['titre' => 'Fiche pratique — Réorganiser son temps sans culpabiliser', 'description' => 'Des pistes concrètes pour alléger son emploi du temps et souffler.'],
        ],
        'violences_sexistes' => [
            ['titre' => 'Fiche réflexive — Reconnaître les violences sexistes', 'description' => "Des exemples concrets pour t'aider à identifier les comportements problématiques."],
            ['titre' => 'Fiche pratique — Réagir et se faire accompagner', 'description' => 'Les étapes possibles pour signaler une situation et obtenir du soutien.'],
        ],
        'violences_sexuelles' => [
            ['titre' => 'Fiche réflexive — Comprendre le consentement', 'description' => "Des repères clairs sur ce qu'est (et n'est pas) le consentement."],
            ['titre' => 'Fiche pratique — Les démarches après une agression', 'description' => "Un panorama des options (médicales, juridiques, psychologiques) sans obligation d'agir tout de suite."],
        ],
        'harcelement' => [
            ['titre' => 'Fiche réflexive — Documenter une situation de harcèlement', 'description' => 'Un guide pour consigner les faits (dates, messages, témoins) utile en cas de signalement.'],
            ['titre' => 'Fiche pratique — Agir en tant que témoin', 'description' => 'Des conseils pour soutenir une personne harcelée sans se mettre en danger.'],
        ],
    ];

    public function run(): void
    {
        $sousThemeIds = SousTheme::pluck('id', 'ref');

        foreach (self::DOCUMENTS as $ref => $documents) {
            $sousThemeId = $sousThemeIds[$ref]
                ?? throw new RuntimeException("Sous-theme inconnu en base : {$ref} (MediaSeeder)");

            $pivot = [];
            foreach ($documents as $ordre => $document) {
                $media = Media::updateOrCreate(
                    ['libelle' => $document['titre']],
                    [
                        'description' => $document['description'],
                        'chemin' => '',
                        'type' => MediaType::Document,
                    ]
                );

                $pivot[$media->id] = ['ordre' => $ordre];
            }

            SousTheme::find($sousThemeId)->medias()->syncWithoutDetaching($pivot);
        }
    }
}
