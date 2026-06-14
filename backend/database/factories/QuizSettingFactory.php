<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_by'        => User::factory(),
            'title'             => 'Quiz ' . fake()->words(3, true),
            'description'       => fake()->sentence(),
            'total_questions'   => fake()->numberBetween(5, 20),
            'duration_minutes'  => fake()->numberBetween(10, 60),
            'difficulty'        => fake()->randomElement(['mudah', 'sedang', 'sulit', 'campuran']),
            'shuffle_questions' => true,
            'shuffle_options'   => true,
            'is_active'         => true,
        ];
    }
}
