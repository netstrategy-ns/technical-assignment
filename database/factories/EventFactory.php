<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('+5 days', '+6 months');
        $endsAt = (clone $startsAt)->modify('+' . rand(2, 5) . ' hours');
        $salesStartAt = (clone $startsAt)->modify('-' . rand(1, 30) . ' days');
        $category = $this->faker->randomElement([
            'Concert',
            'Sports',
            'Theater',
            'Festival',
            'Comedy',
            'Conference',
        ]);
        $city = $this->faker->randomElement([
            'Milano',
            'Roma',
            'Torino',
            'Bologna',
            'Firenze',
            'Napoli',
            'Venezia',
        ]);

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraphs(3, true),
            'category' => $category,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'venue' => $this->faker->company . ' Arena',
            'city' => $city,
            'image_url' => null,
            'is_featured' => $this->faker->boolean(30),
            'sales_start_at' => $salesStartAt,
            'queue_enabled' => $this->faker->boolean(25),
            'queue_max_concurrent' => $this->faker->boolean(25)
                ? $this->faker->numberBetween(50, 200)
                : null,
        ];
    }
}
