<?php

namespace Tests\Feature\Filament;

use App\Enums\MediaType;
use App\Enums\UserRole;
use App\Filament\Resources\MediaResource\Pages\CreateMedia;
use App\Filament\Resources\MediaResource\Pages\EditMedia;
use App\Filament\Resources\MediaResource\Pages\ListMedia;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MediaResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_compte_sans_role_est_refuse_sur_la_liste(): void
    {
        $this->actingAs(User::factory()->create(['role' => null]));

        Livewire::test(ListMedia::class)->assertForbidden();
    }

    public function test_un_webmaster_accede_a_la_liste(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));

        Livewire::test(ListMedia::class)->assertSuccessful();
    }

    public function test_un_redacteur_accede_aussi_a_la_liste(): void
    {
        // Seule ressource Filament partagee entre les deux roles — voir
        // docs/schema_bd.md § Rôles.
        $this->actingAs(User::factory()->create(['role' => UserRole::Redacteur]));

        Livewire::test(ListMedia::class)->assertSuccessful();
    }

    public function test_un_redacteur_cree_un_media(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Redacteur]));

        Livewire::test(CreateMedia::class)
            ->fillForm([
                'libelle' => 'Fiche réflexive test',
                'description' => 'Description de test.',
                'chemin' => 'documents/fiche-test.pdf',
                'type' => MediaType::Document->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medias', [
            'libelle' => 'Fiche réflexive test',
            'description' => 'Description de test.',
            'type' => MediaType::Document->value,
        ]);
    }

    public function test_un_webmaster_edite_la_description(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));
        $media = Media::factory()->type(MediaType::Document)->create(['description' => 'Avant.']);

        Livewire::test(EditMedia::class, ['record' => $media->getKey()])
            ->fillForm(['description' => 'Après.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medias', ['id' => $media->id, 'description' => 'Après.']);
    }
}
