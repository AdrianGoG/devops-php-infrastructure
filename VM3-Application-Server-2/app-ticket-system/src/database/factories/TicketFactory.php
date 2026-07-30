<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => strtoupper($this->faker->unique()->bothify('TCK-####')),
            'subject' => ucfirst($this->faker->words(5, true)),
            'description' => $this->faker->paragraph(),
            'requester' => $this->faker->safeEmail(),
            'priority' => $this->faker->randomElement(Ticket::PRIORITIES),
            'status' => $this->faker->randomElement(Ticket::STATUSES),
            'assignee' => $this->faker->randomElement(['Ana Pop', 'Radu Dinu', null]),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'open']);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'closed',
            'resolved_at' => now(),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes): array => ['priority' => 'urgent']);
    }
}
