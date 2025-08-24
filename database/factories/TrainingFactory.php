<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training>
 */
class TrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title" => fake()->word,
            "description" => fake()->text,
            "deadline_days" => fake()->numberBetween(10, 30),
            "certificate" => "https://res.cloudinary.com/dnzuptxvy/image/upload/v1756020737/nest-uploads/dikw1qg5m3loedlk1xxi.pdf"
        ];
    }
}
