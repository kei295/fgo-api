<?php

namespace Database\Factories;

use App\Models\Servant;
use App\Models\User;
use App\Models\UserServant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserServant>
 */
class UserServantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'servant_id' => Servant::factory(),
            'level' => 1,
            'skill_1' => 1,
            'skill_2' => 1,
            'skill_3' => 1,
            'bond_level' => 0,
            'np_level' => 1,
            'ascension' => 0,
        ];
    }
}
