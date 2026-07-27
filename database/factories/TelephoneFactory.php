<?php

namespace Database\Factories;

use App\Enums\TelephoneType;
use App\Models\Contact;
use App\Models\Telephone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Telephone>
 */
class TelephoneFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero' => fake()->numerify('## ## ##'),
            'type' => fake()->randomElement(TelephoneType::cases()),
            'libelle' => null,
            'contact_id' => Contact::factory(),
        ];
    }

    /**
     * Ligne d'urgence joignable en continu.
     */
    public function urgence(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TelephoneType::Urgence,
            'numero' => fake()->numerify('##'),
        ]);
    }

    /**
     * Ligne identifiee au sein d'une meme structure : psychologue, intervenant social.
     */
    public function libelle(string $libelle): static
    {
        return $this->state(fn (array $attributes) => [
            'libelle' => $libelle,
        ]);
    }
}
