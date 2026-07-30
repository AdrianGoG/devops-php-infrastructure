<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = ucfirst($this->faker->words(5, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 100000),
            'excerpt' => $this->faker->sentence(12),
            'body' => $this->faker->paragraphs(4, true),
            'status' => 'published',
            'author' => $this->faker->name(),
            'published_at' => now()->subDays($this->faker->numberBetween(1, 120)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}