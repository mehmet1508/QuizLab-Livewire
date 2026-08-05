<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'baslik' => fake()->sentence(3),
            'aciklama' => fake()->paragraph(),
            'sure_dakika' => null,
            'sure_tipi' => 'sabit',
            'sure_birimi' => 'dakika',
            'kategori_id' => Category::factory(),
        ];
    }
}
