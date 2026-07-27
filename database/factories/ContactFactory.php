<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Par defaut une structure : « prenom » reste vide, c'est le cas majoritaire.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nom = fake()->unique()->company();

        return [
            'ref' => Str::slug($nom, '_'),
            'nom' => $nom,
            'prenom' => null,
            'mail' => fake()->safeEmail(),
            'localisation' => fake()->city(),
            'site_web' => fake()->domainName(),
            'horaires' => 'Lun-Ven 8h-17h',
            'remarques' => fake()->sentence(),
            'gratuit' => true,
            'anonyme' => true,
            'actif' => true,
        ];
    }

    /**
     * Referent nomme : donnees personnelles, concerne par le droit a l'effacement.
     */
    public function referentNomme(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
        ]);
    }

    /**
     * Structure fermee : reste en base, disparait du front.
     */
    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => [
            'actif' => false,
        ]);
    }

    /**
     * Extraction incomplete : les criteres filtrables ne sont pas renseignes.
     */
    public function criteresInconnus(): static
    {
        return $this->state(fn (array $attributes) => [
            'gratuit' => null,
            'anonyme' => null,
        ]);
    }
}
