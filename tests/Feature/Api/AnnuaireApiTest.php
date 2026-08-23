<?php

namespace Tests\Feature\Api;

use App\Enums\MediaType;
use App\Models\Contact;
use App\Models\Media;
use App\Models\SousTheme;
use App\Models\Telephone;
use App\Models\Theme;
use Database\Seeders\MediaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnuaireApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_themes_liste_les_sous_themes_avec_resume_mais_sans_article_ni_contacts(): void
    {
        $theme = Theme::factory()->create(['ref' => 'addictions_test', 'libelle' => 'Addictions']);
        SousTheme::factory()->create([
            'theme_id' => $theme->id,
            'ref' => 'alcool_test',
            'libelle' => 'Alcool',
            'resume' => 'Teaser alcool.',
        ]);

        $response = $this->getJson('/api/themes');

        $response->assertOk();
        // Recherche par ref plutot que data.0 : la taxonomie de reference
        // (migration 2026_07_31_120000_seed_taxonomie_themes_sous_themes)
        // peuple deja /api/themes independamment de ce test.
        $data = collect($response->json('data'))->firstWhere('ref', 'addictions_test');
        $this->assertNotNull($data);
        $this->assertSame('alcool_test', $data['sous_themes'][0]['ref']);
        $this->assertSame('Teaser alcool.', $data['sous_themes'][0]['resume']);
        $this->assertArrayNotHasKey('article', $data['sous_themes'][0]);
    }

    public function test_get_themes_embarque_le_resume_et_les_medias_du_theme(): void
    {
        $theme = Theme::factory()->create(['ref' => 'addictions_test', 'resume' => 'Texte de presentation.']);
        $video = Media::factory()->type(MediaType::Video)->create(['libelle' => 'Intro addictions']);
        $theme->medias()->attach($video->id);

        $response = $this->getJson('/api/themes');

        $response->assertOk();
        $data = collect($response->json('data'))->firstWhere('ref', 'addictions_test');
        $this->assertNotNull($data);
        $this->assertSame('Texte de presentation.', $data['resume']);
        $this->assertSame('Intro addictions', $data['medias'][0]['libelle']);
        $this->assertSame('video', $data['medias'][0]['type']);
    }

    public function test_get_themes_expose_le_libelle_court_mais_pas_l_ordre(): void
    {
        Theme::factory()->create(['ref' => 'addictions_test', 'libelle_court' => 'Addictions']);
        SousTheme::factory()->create(['ref' => 'alcool_test', 'theme_id' => Theme::where('ref', 'addictions_test')->value('id')]);

        $response = $this->getJson('/api/themes');

        $response->assertOk();
        $data = collect($response->json('data'))->firstWhere('ref', 'addictions_test');
        $this->assertNotNull($data);
        $this->assertSame('Addictions', $data['libelle_court']);
        // Le tri se fait en base (Theme::ordonne(), SousTheme::sousThemes()) :
        // le tableau arrive deja ordonne, la valeur brute n'a rien a faire
        // dans un payload que le front consomme deja trie.
        $this->assertArrayNotHasKey('ordre', $data);
        $this->assertArrayNotHasKey('ordre', $data['sous_themes'][0]);
    }

    public function test_get_sous_theme_par_ref_renvoie_l_article_et_les_contacts_actifs_sans_resume(): void
    {
        $sousTheme = SousTheme::factory()->create(['ref' => 'alcool_test', 'article' => 'Contenu.', 'resume' => 'Teaser.']);
        $contactActif = Contact::factory()->create(['nom' => 'CSAPA']);
        $contactInactif = Contact::factory()->inactif()->create(['nom' => 'Ferme']);
        $sousTheme->contacts()->attach($contactActif->id, ['ordre' => 0]);
        $sousTheme->contacts()->attach($contactInactif->id, ['ordre' => 1]);

        $response = $this->getJson('/api/sous-themes/alcool_test');

        $response->assertOk();
        $response->assertJsonPath('data.ref', 'alcool_test');
        $response->assertJsonPath('data.article', 'Contenu.');
        $response->assertJsonMissingPath('data.resume');
        $response->assertJsonCount(1, 'data.contacts');
        $response->assertJsonPath('data.contacts.0.nom', 'CSAPA');
    }

    public function test_get_sous_theme_embarque_le_theme_parent_de_facon_minimale(): void
    {
        $theme = Theme::factory()->create([
            'ref' => 'addictions_test',
            'libelle_court' => 'Addictions',
            'resume' => 'Texte de presentation du theme.',
        ]);
        SousTheme::factory()->create(['ref' => 'alcool_test', 'theme_id' => $theme->id]);

        $response = $this->getJson('/api/sous-themes/alcool_test');

        $response->assertOk();
        $response->assertJsonPath('data.theme.ref', 'addictions_test');
        $response->assertJsonPath('data.theme.libelle_court', 'Addictions');
        // Le resume et les medias du theme alimentent l'accueil, pas le tag
        // de la fiche : reutiliser ThemeResource ici les ferait fuiter.
        $this->assertArrayNotHasKey('resume', $response->json('data.theme'));
        $this->assertArrayNotHasKey('medias', $response->json('data.theme'));
        $this->assertArrayNotHasKey('sous_themes', $response->json('data.theme'));
    }

    public function test_get_sous_theme_n_expose_pas_l_ordre(): void
    {
        SousTheme::factory()->create(['ref' => 'alcool_test']);

        $response = $this->getJson('/api/sous-themes/alcool_test');

        $response->assertOk();
        $this->assertArrayNotHasKey('ordre', $response->json('data'));
    }

    public function test_get_sous_theme_expose_l_intro_ressources_et_les_documents_ordonnes(): void
    {
        $sousTheme = SousTheme::factory()->create([
            'ref' => 'alcool_test',
            'intro_ressources' => 'Chapeau de la page ressources.',
        ]);
        $second = Media::factory()->type(MediaType::Document)->create(['libelle' => 'Fiche B', 'description' => 'Deuxieme.']);
        $premier = Media::factory()->type(MediaType::Document)->create(['libelle' => 'Fiche A', 'description' => 'Premiere.']);
        $sousTheme->medias()->attach($second->id, ['ordre' => 1]);
        $sousTheme->medias()->attach($premier->id, ['ordre' => 0]);

        $response = $this->getJson('/api/sous-themes/alcool_test');

        $response->assertOk();
        $response->assertJsonPath('data.intro_ressources', 'Chapeau de la page ressources.');
        $response->assertJsonCount(2, 'data.documents');
        $response->assertJsonPath('data.documents.0.libelle', 'Fiche A');
        $response->assertJsonPath('data.documents.0.description', 'Premiere.');
        $response->assertJsonPath('data.documents.1.libelle', 'Fiche B');
    }

    public function test_get_sous_theme_par_ref_inconnue_renvoie_404(): void
    {
        $this->getJson('/api/sous-themes/inconnu')->assertNotFound();
    }

    public function test_le_contenu_editorial_seede_est_servi_par_la_fiche_reelle(): void
    {
        // Vise le ref 'alcool' de la taxonomie reelle plutot qu'un ref _test :
        // verifie que le seeder est effectivement rejoue, pas juste que le
        // modele sait porter ces colonnes. article/intro_ressources viennent
        // de la migration 2026_07_31 (toujours rejouee par RefreshDatabase) ;
        // documents vient de MediaSeeder, appele depuis DatabaseSeeder — a
        // seeder explicitement, comme ContactSeeder.
        $this->seed(MediaSeeder::class);

        $response = $this->getJson('/api/sous-themes/alcool');

        $response->assertOk();
        $response->assertJsonPath('data.theme.ref', 'addictions');
        $response->assertJsonPath('data.theme.libelle_court', 'Addictions');
        $this->assertNotEmpty($response->json('data.article'));
        $this->assertNotEmpty($response->json('data.intro_ressources'));
        $response->assertJsonCount(2, 'data.documents');
    }

    public function test_get_sous_theme_embarque_les_telephones_des_contacts_avec_numero_vert(): void
    {
        $sousTheme = SousTheme::factory()->create(['ref' => 'urgences']);
        $contact = Contact::factory()->create();
        $sousTheme->contacts()->attach($contact->id, ['ordre' => 0]);
        Telephone::factory()->numeroVert()->libelle('Ecoute')->create(['contact_id' => $contact->id]);

        $response = $this->getJson('/api/sous-themes/urgences');

        $response->assertOk();
        $response->assertJsonPath('data.contacts.0.telephones.0.libelle', 'Ecoute');
        $response->assertJsonPath('data.contacts.0.telephones.0.numero_vert', true);
    }
}
