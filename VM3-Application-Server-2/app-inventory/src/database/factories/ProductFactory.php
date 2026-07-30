<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => strtoupper($this->faker->unique()->bothify('??-####')),
            'name' => ucfirst($this->faker->words(2, true)),
            'quantity' => $this->faker->numberBetween(0, 400),
            'reorder_level' => $this->faker->numberBetween(5, 40),
            'unit_price' => $this->faker->randomFloat(2, 1, 500),
            'location' => $this->faker->randomElement(['A-01', 'A-02', 'B-11', 'C-04', null]),
        ];
    }

    /**
     * A product that has to be reordered.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => 2,
            'reorder_level' => 10,
        ]);
    }
}
