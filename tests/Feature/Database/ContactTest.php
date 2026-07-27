<?php

namespace Tests\Feature\Database;

use App\Enums\TelephoneType;
use App\Models\Contact;
use App\Models\SousTheme;
use App\Models\Telephone;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_contact_peut_exister_sans_sous_theme(): void
    {
        // Etat normal entre la creation et l'assignation. Invisible du front.
        $contact = Contact::factory()->create();

        $this->assertCount(0, $contact->sousThemes);
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_un_contact_sert_plusieurs_sous_themes_et_inversement(): void
    {
        $contact = Contact::factory()->create();
        $sousThemes = SousTheme::factory()->count(3)->create();
        $contact->sousThemes()->attach($sousThemes->pluck('id'), ['ordre' => 0]);

        $autre = Contact::factory()->create();
        $autre->sousThemes()->attach($sousThemes->first()->id, ['ordre' => 0]);

        $this->assertCount(3, $contact->sousThemes);
        $this->assertCount(2, $sousThemes->first()->contacts);
    }

    public function test_l_ordre_du_pivot_pilote_l_affichage(): void
    {
        $sousTheme = SousTheme::factory()->create();
        $universitaire = Contact::factory()->create(['nom' => 'SUMPPS']);
        $associatif = Contact::factory()->create(['nom' => 'Association']);

        // Les contacts universitaires passent en tete.
        $sousTheme->contacts()->attach($associatif->id, ['ordre' => 10]);
        $sousTheme->contacts()->attach($universitaire->id, ['ordre' => 1]);

        $this->assertSame(
            ['SUMPPS', 'Association'],
            $sousTheme->contacts()->pluck('nom')->all()
        );
    }

    public function test_un_contact_possede_plusieurs_telephones_libelles(): void
    {
        $contact = Contact::factory()->create();
        Telephone::factory()->libelle('Psychologue')->create(['contact_id' => $contact->id]);
        Telephone::factory()->libelle('Intervenant social')->create(['contact_id' => $contact->id]);

        $this->assertCount(2, $contact->telephones);
        $this->assertContains('Psychologue', $contact->telephones->pluck('libelle')->all());
    }

    public function test_le_type_de_telephone_est_caste_en_enum(): void
    {
        $telephone = Telephone::factory()->urgence()->create();

        $this->assertInstanceOf(TelephoneType::class, $telephone->fresh()->type);
        $this->assertSame(TelephoneType::Urgence, $telephone->fresh()->type);
    }

    public function test_le_type_de_telephone_rejette_une_valeur_hors_liste(): void
    {
        $contact = Contact::factory()->create();

        $this->expectException(QueryException::class);

        \DB::table('telephones')->insert([
            'numero' => '00',
            'type' => 'fax',
            'contact_id' => $contact->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_supprimer_un_contact_efface_ses_donnees_liees_rgpd(): void
    {
        // Droit a l'effacement : nom, prenom et mail d'un referent nomme.
        $contact = Contact::factory()->referentNomme()->create();
        $sousTheme = SousTheme::factory()->create();
        $contact->sousThemes()->attach($sousTheme->id, ['ordre' => 0]);
        Telephone::factory()->count(2)->create(['contact_id' => $contact->id]);

        $contact->delete();

        $this->assertDatabaseCount('contacts', 0);
        $this->assertDatabaseCount('telephones', 0);
        $this->assertDatabaseCount('contact_sous_theme', 0);
        // Le sous-theme, lui, survit.
        $this->assertDatabaseCount('sous_themes', 1);
    }

    public function test_le_scope_actif_ecarte_les_structures_fermees(): void
    {
        Contact::factory()->count(2)->create();
        Contact::factory()->inactif()->create();

        $this->assertSame(3, Contact::count());
        $this->assertSame(2, Contact::actif()->count());
    }

    public function test_les_criteres_filtrables_distinguent_inconnu_de_faux(): void
    {
        $inconnu = Contact::factory()->criteresInconnus()->create();
        $payant = Contact::factory()->create(['gratuit' => false]);

        $this->assertNull($inconnu->fresh()->gratuit);
        $this->assertFalse($payant->fresh()->gratuit);
    }
}
