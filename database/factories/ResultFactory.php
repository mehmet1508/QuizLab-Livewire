<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Result>
 */
class ResultFactory extends Factory
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
            'quiz_id' => Quiz::factory(),
            'puan' => fake()->numberBetween(0, 100),
            'sure_saniye' => fake()->numberBetween(10, 300),
        ];
    }
}
