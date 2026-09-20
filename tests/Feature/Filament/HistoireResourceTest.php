<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\HistoireResource\Pages\CreateHistoire;
use App\Filament\Resources\HistoireResource\Pages\EditHistoire;
use App\Filament\Resources\HistoireResource\Pages\ListHistoires;
use App\Models\Histoire;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class HistoireResourceTest extends TestCase
{
    public function test_un_webmaster_est_refuse_sur_la_liste(): void
    {
        // Inverse de ContactResource/ThemeResource — voir docs/schema_bd.md § Rôles.
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));

        Livewire::test(ListHistoires::class)->assertForbidden();
    }

    public function test_un_compte_sans_role_est_refuse_sur_la_liste(): void
    {
        $this->actingAs(User::factory()->create(['role' => null]));

        Livewire::test(ListHistoires::class)->assertForbidden();
    }

    public function test_un_redacteur_accede_a_la_liste(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Redacteur]));

        Livewire::test(ListHistoires::class)->assertSuccessful();
    }

    public function test_un_redacteur_cree_une_histoire(): void
    {
        $redacteur = User::factory()->create(['role' => UserRole::Redacteur]);
        $this->actingAs($redacteur);

        Livewire::test(CreateHistoire::class)
            ->fillForm([
                'titre' => 'Une soirée qui dérape',
                'etat' => 'brouillon',
                'user_id' => $redacteur->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('histoires', [
            'ref' => 'une_soiree_qui_derape',
            'titre' => 'Une soirée qui dérape',
        ]);
    }

    public function test_le_ref_reste_inchange_a_l_edition(): void
    {
        $redacteur = User::factory()->create(['role' => UserRole::Redacteur]);
        $this->actingAs($redacteur);
        $histoire = Histoire::factory()->create(['ref' => 'histoire_test', 'titre' => 'Histoire test']);

        Livewire::test(EditHistoire::class, ['record' => $histoire->getKey()])
            ->fillForm(['titre' => 'Histoire test (modifiée)'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('histoire_test', $histoire->fresh()->ref);
    }
}
