<?php

namespace Tests\Feature\Filament;

use App\Enums\TelephoneType;
use App\Enums\UserRole;
use App\Filament\Resources\ContactResource\Pages\CreateContact;
use App\Filament\Resources\ContactResource\Pages\EditContact;
use App\Filament\Resources\ContactResource\Pages\ListContacts;
use App\Filament\Resources\ContactResource\RelationManagers\SousThemesRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\TelephonesRelationManager;
use App\Models\Contact;
use App\Models\SousTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_redacteur_est_refuse_sur_la_liste(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Redacteur]));

        Livewire::test(ListContacts::class)->assertForbidden();
    }

    public function test_un_webmaster_accede_a_la_liste(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));

        Livewire::test(ListContacts::class)->assertSuccessful();
    }

    public function test_un_webmaster_cree_un_contact_sans_sous_theme(): void
    {
        // Etat normal entre la creation et l'assignation — voir docs/schema_bd.md § Contact.
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));

        Livewire::test(CreateContact::class)
            ->fillForm([
                'ref' => 'samu',
                'nom' => 'SAMU',
                'actif' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', ['ref' => 'samu', 'nom' => 'SAMU']);
    }

    public function test_le_ref_est_verrouille_en_edition(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));
        $contact = Contact::factory()->create(['ref' => 'sos_ecoute', 'nom' => 'SOS Ecoute']);

        Livewire::test(EditContact::class, ['record' => $contact->getKey()])
            ->fillForm(['ref' => 'autre_ref', 'nom' => 'SOS Ecoute (modifie)'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'ref' => 'sos_ecoute', 'nom' => 'SOS Ecoute (modifie)']);
    }

    public function test_le_relation_manager_telephones_cree_un_numero(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));
        $contact = Contact::factory()->create();

        Livewire::test(TelephonesRelationManager::class, [
            'ownerRecord' => $contact,
            'pageClass' => EditContact::class,
        ])
            ->callTableAction('create', data: [
                'numero' => '15',
                'type' => TelephoneType::Urgence->value,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('telephones', ['contact_id' => $contact->id, 'numero' => '15']);
    }

    public function test_le_relation_manager_sous_themes_attache_avec_un_ordre(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));
        $contact = Contact::factory()->create();
        $sousTheme = SousTheme::factory()->create();

        Livewire::test(SousThemesRelationManager::class, [
            'ownerRecord' => $contact,
            'pageClass' => EditContact::class,
        ])
            ->callTableAction('attach', data: [
                'recordId' => $sousTheme->getKey(),
                'ordre' => 2,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('contact_sous_theme', [
            'contact_id' => $contact->id,
            'sous_theme_id' => $sousTheme->id,
            'ordre' => 2,
        ]);
    }

    public function test_un_webmaster_geocode_un_contact(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));
        $contact = Contact::factory()->create(['latitude' => null, 'longitude' => null]);

        Livewire::test(EditContact::class, ['record' => $contact->getKey()])
            ->fillForm(['latitude' => -22.2758, 'longitude' => 166.4580])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'latitude' => -22.2758, 'longitude' => 166.4580]);
    }
}
