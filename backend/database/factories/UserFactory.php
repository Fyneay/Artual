<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'id' => $this->faker->unique()->numberBetween(1,800),
            'nickname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->password('6','10'),
            'role_id' => DB::table('users_groups')->inRandomOrder()->first()->id
        ];
    }
}
