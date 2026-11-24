<?php

namespace Database\Factories;

use App\Models\Servant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servant>
 */
class ServantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = fake()->unique()->numberBetween(100000, 999999);

        return [
            'id' => $id,
            'collection_no' => $id,
            'name' => fake()->name(),
            'class_name' => fake()->randomElement(['Saber', 'Archer', 'Lancer', 'Rider', 'Caster', 'Assassin', 'Berserker']),
            'rarity' => fake()->numberBetween(1, 5),
            'attribute' => fake()->randomElement(['天', '地', '人', '星', '獣']),
            'np_name' => fake()->words(3, true),
            'np_type' => fake()->randomElement(['Quick', 'Arts', 'Buster']),
            'bond_ce_id' => fake()->numberBetween(9000000, 9999999),
            'bond_ce_name' => fake()->words(2, true),
            'face_url' => fake()->imageUrl(),
            'ascension_image_urls' => [fake()->imageUrl(), fake()->imageUrl(), fake()->imageUrl(), fake()->imageUrl()],
            'bond_ce_image_url' => fake()->imageUrl(),
        ];
    }
}
