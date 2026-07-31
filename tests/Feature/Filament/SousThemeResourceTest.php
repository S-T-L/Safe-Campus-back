<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\SousThemeResource\Pages\CreateSousTheme;
use App\Filament\Resources\SousThemeResource\Pages\EditSousTheme;
use App\Filament\Resources\SousThemeResource\Pages\ListSousThemes;
use App\Models\SousTheme;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SousThemeResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_redacteur_est_refuse_sur_la_liste(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Redacteur]));

        Livewire::test(ListSousThemes::class)->assertForbidden();
    }

    public function test_un_webmaster_accede_a_la_liste(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));

        Livewire::test(ListSousThemes::class)->assertSuccessful();
    }

    public function test_un_webmaster_cree_un_sous_theme_rattache_a_un_theme(): void
    {
        // Refs distinctes de la taxonomie posee par la migration
        // 2026_07_31_120000_seed_taxonomie_themes_sous_themes (deja en base
        // via RefreshDatabase) : eviter toute collision sur `ref`.
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));
        $theme = Theme::factory()->create(['ref' => 'theme_parent_test']);

        Livewire::test(CreateSousTheme::class)
            ->fillForm([
                'theme_id' => $theme->id,
                'ref' => 'sous_theme_test',
                'libelle' => 'Sous-thème de test',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sous_themes', ['ref' => 'sous_theme_test', 'theme_id' => $theme->id]);
    }

    public function test_le_ref_est_verrouille_en_edition(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Webmaster]));
        $sousTheme = SousTheme::factory()->create(['ref' => 'sous_theme_verrouille', 'libelle' => 'Avant modification']);

        Livewire::test(EditSousTheme::class, ['record' => $sousTheme->getKey()])
            ->fillForm(['ref' => 'autre_ref', 'libelle' => 'Après modification'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sous_themes', ['id' => $sousTheme->id, 'ref' => 'sous_theme_verrouille', 'libelle' => 'Après modification']);
    }
}
