<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Theme/SousTheme : taxonomie de reference, seedee par la migration
        // 2026_07_31_120000_seed_taxonomie_themes_sous_themes — pas ici.
        $this->call([
            ContactSeeder::class,
        ]);
    }
}
