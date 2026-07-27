<?php

namespace Database\Factories;

use App\Models\Signalement;
use App\Models\SousTheme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Signalement>
 */
class SignalementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token_antispam' => Str::random(40),
            'sous_theme_id' => SousTheme::factory()->avecSignalement(),
            'texte' => fake()->paragraph(),
            'date_heure' => now(),
        ];
    }
}
