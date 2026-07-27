<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Theme>
 */
class ThemeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $libelle = fake()->unique()->words(2, true);

        return [
            'ref' => Str::slug($libelle, '_'),
            'libelle' => Str::ucfirst($libelle),
        ];
    }
}
