<?php

namespace Tests\Feature\Api;

use App\Enums\MediaType;
use App\Models\Contact;
use App\Models\Media;
use App\Models\SousTheme;
use App\Models\Telephone;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnuaireApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_themes_liste_les_sous_themes_avec_resume_mais_sans_article_ni_contacts(): void
    {
        $theme = Theme::factory()->create(['ref' => 'addictions', 'libelle' => 'Addictions']);
        SousTheme::factory()->create([
            'theme_id' => $theme->id,
            'ref' => 'alcool',
            'libelle' => 'Alcool',
            'resume' => 'Teaser alcool.',
        ]);

        $response = $this->getJson('/api/themes');

        $response->assertOk();
        $response->assertJsonPath('data.0.ref', 'addictions');
        $response->assertJsonPath('data.0.sous_themes.0.ref', 'alcool');
        $response->assertJsonPath('data.0.sous_themes.0.resume', 'Teaser alcool.');
        $response->assertJsonMissingPath('data.0.sous_themes.0.article');
    }

    public function test_get_themes_embarque_le_resume_et_les_medias_du_theme(): void
    {
        $theme = Theme::factory()->create(['ref' => 'addictions', 'resume' => 'Texte de presentation.']);
        $video = Media::factory()->type(MediaType::Video)->create(['libelle' => 'Intro addictions']);
        $theme->medias()->attach($video->id);

        $response = $this->getJson('/api/themes');

        $response->assertOk();
        $response->assertJsonPath('data.0.resume', 'Texte de presentation.');
        $response->assertJsonPath('data.0.medias.0.libelle', 'Intro addictions');
        $response->assertJsonPath('data.0.medias.0.type', 'video');
    }

    public function test_get_sous_theme_par_ref_renvoie_l_article_et_les_contacts_actifs_sans_resume(): void
    {
        $sousTheme = SousTheme::factory()->create(['ref' => 'alcool', 'article' => 'Contenu.', 'resume' => 'Teaser.']);
        $contactActif = Contact::factory()->create(['nom' => 'CSAPA']);
        $contactInactif = Contact::factory()->inactif()->create(['nom' => 'Ferme']);
        $sousTheme->contacts()->attach($contactActif->id, ['ordre' => 0]);
        $sousTheme->contacts()->attach($contactInactif->id, ['ordre' => 1]);

        $response = $this->getJson('/api/sous-themes/alcool');

        $response->assertOk();
        $response->assertJsonPath('data.ref', 'alcool');
        $response->assertJsonPath('data.article', 'Contenu.');
        $response->assertJsonMissingPath('data.resume');
        $response->assertJsonCount(1, 'data.contacts');
        $response->assertJsonPath('data.contacts.0.nom', 'CSAPA');
    }

    public function test_get_sous_theme_par_ref_inconnue_renvoie_404(): void
    {
        $this->getJson('/api/sous-themes/inconnu')->assertNotFound();
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
