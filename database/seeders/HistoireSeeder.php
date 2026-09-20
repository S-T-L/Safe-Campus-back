<?php

namespace Database\Seeders;

use App\Enums\ChoixIssue;
use App\Enums\EtatHistoire;
use App\Enums\UserRole;
use App\Models\Choix;
use App\Models\Histoire;
use App\Models\Scene;
use App\Models\SousTheme;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Histoire de demonstration « Une soiree qui derape » : 3 scenes, 9 choix,
 * un parcours favorable et un defavorable. Sert a retrouver un jeu de donnees
 * de reference apres un `migrate:fresh --seed`.
 *
 * Idempotent : l'histoire est cherchee par `ref`, une scene par (histoire,
 * titre), un choix par (scene, texte). Rejouer le seeder met a jour sans
 * dupliquer. Jamais en production : c'est du contenu de demo, et il cree un
 * compte redacteur aux identifiants bidon.
 */
class HistoireSeeder extends Seeder
{
    private const REF = 'demo_soiree_qui_derape';

    private const SOUS_THEME_REF = 'alcool';

    private const REDACTEUR_EMAIL = 'redacteur@test.local';

    /**
     * Cle = identifiant interne au seeder, sert a relier les choix aux scenes
     * suivantes. L'ordre du tableau est l'ordre des scenes dans l'histoire.
     *
     * @var array<string, array{titre: string, dialogue_text: string, est_initiale?: bool, choix: list<array{text_choix: string, suivante: ?string, issue: ?ChoixIssue}>}>
     */
    private const SCENES = [
        'debut' => [
            'titre' => '[DEMO] Début de soirée',
            'dialogue_text' => "Tu arrives à une soirée entre amis. La musique est forte, tout le monde a l'air de bien s'amuser. Un inconnu te tend un verre en souriant.",
            'est_initiale' => true,
            'choix' => [
                ['text_choix' => 'Accepter le verre avec un sourire', 'suivante' => 'verre', 'issue' => null],
                ['text_choix' => 'Remercier poliment mais refuser, tu préfères rester avec tes amis', 'suivante' => 'fin', 'issue' => null],
                ['text_choix' => 'Cette soirée ne te dit rien, tu rentres directement', 'suivante' => null, 'issue' => ChoixIssue::Favorable],
            ],
        ],
        'verre' => [
            'titre' => '[DEMO] Le verre suspect',
            'dialogue_text' => "Tu regardes le verre qu'on vient de te tendre. Tu n'as pas vu comment il a été préparé, ni ce qu'il contient vraiment.",
            'choix' => [
                ['text_choix' => 'Boire le verre sans poser de questions', 'suivante' => null, 'issue' => ChoixIssue::Defavorable],
                ['text_choix' => 'Refuser et demander à un ami de rester avec toi', 'suivante' => 'fin', 'issue' => null],
                ['text_choix' => 'Prévenir un agent de sécurité de la soirée', 'suivante' => null, 'issue' => ChoixIssue::Favorable],
            ],
        ],
        'fin' => [
            'titre' => '[DEMO] Fin de soirée',
            'dialogue_text' => 'Il est tard. La soirée touche à sa fin, et il faut maintenant penser à rentrer.',
            'choix' => [
                ['text_choix' => 'Rentrer ensemble avec tes amis', 'suivante' => null, 'issue' => ChoixIssue::Favorable],
                ['text_choix' => "Laisser un ami rentrer seul alors qu'il a trop bu", 'suivante' => null, 'issue' => ChoixIssue::Defavorable],
                ['text_choix' => 'Appeler un taxi pour tout le monde', 'suivante' => null, 'issue' => ChoixIssue::Favorable],
            ],
        ],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $redacteur = User::firstOrCreate(
            ['email' => self::REDACTEUR_EMAIL],
            [
                'name' => 'Rédacteur test',
                'password' => 'password',
                'role' => UserRole::Redacteur,
            ],
        );

        $histoire = Histoire::updateOrCreate(
            ['ref' => self::REF],
            [
                'titre' => 'Une soirée qui dérape (démo)',
                'etat' => EtatHistoire::Publie,
                'user_id' => $redacteur->id,
            ],
        );

        $sousTheme = SousTheme::where('ref', self::SOUS_THEME_REF)->first();
        $histoire->sousThemes()->sync($sousTheme ? [$sousTheme->id] : []);

        // Premier passage : les scenes et leur rattachement. Les choix viennent
        // apres, une scene suivante devant exister pour etre referencee.
        $scenes = [];
        $rang = 1;

        foreach (self::SCENES as $cle => $donnees) {
            $scene = $histoire->scenes()->where('titre', $donnees['titre'])->first()
                ?? Scene::create(['titre' => $donnees['titre'], 'dialogue_text' => $donnees['dialogue_text']]);

            $scene->update(['dialogue_text' => $donnees['dialogue_text']]);

            $histoire->scenes()->syncWithoutDetaching([
                $scene->id => [
                    'est_initiale' => $donnees['est_initiale'] ?? false,
                    'ordre' => $rang++,
                ],
            ]);

            $scenes[$cle] = $scene;
        }

        foreach (self::SCENES as $cle => $donnees) {
            foreach ($donnees['choix'] as $choix) {
                Choix::updateOrCreate(
                    ['scene_id' => $scenes[$cle]->id, 'text_choix' => $choix['text_choix']],
                    [
                        'next_scene_id' => $choix['suivante'] ? $scenes[$choix['suivante']]->id : null,
                        'issue' => $choix['issue'],
                    ],
                );
            }
        }
    }
}
