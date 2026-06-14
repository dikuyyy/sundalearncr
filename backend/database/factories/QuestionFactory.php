<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['sunda_to_latin', 'latin_to_sunda', 'multiple_choice']);

        return [
            'created_by'     => User::factory(),
            'type'           => $type,
            'difficulty'     => fake()->randomElement(['mudah', 'sedang', 'sulit']),
            'question_text'  => fake()->sentence(),
            'correct_answer' => 'a',
            'options'        => $type === 'multiple_choice'
                ? ['a' => 'Jawaban A', 'b' => 'Jawaban B', 'c' => 'Jawaban C', 'd' => 'Jawaban D']
                : null,
            'is_active'      => true,
        ];
    }
}
