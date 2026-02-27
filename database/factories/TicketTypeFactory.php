<?php

namespace Database\Factories;

use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketType>
 */
class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Standard', 'VIP', 'Premium', 'Backstage']),
            'price' => $this->faker->randomFloat(2, 20, 300),
            'total_quantity' => $this->faker->numberBetween(50, 800),
            'max_per_user' => $this->faker->randomElement([2, 4, 6, null]),
        ];
    }
}
