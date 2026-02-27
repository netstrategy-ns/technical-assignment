<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $events = [
            // Featured, sale started, no queue
            [
                'category_slug' => 'concerts',
                'title' => 'Rock Night 2026',
                'description' => "An unforgettable evening of rock music featuring Italy's top bands. Expect electrifying performances, stunning light shows, and a night that will go down in history.",
                'slug' => 'rock-night-2026',
                'venue' => 'San Siro Stadium',
                'city' => 'Milan',
                'starts_at' => now()->addDays(30)->setTime(20, 0),
                'ends_at' => now()->addDays(30)->setTime(23, 30),
                'sale_starts_at' => now()->subDays(5),
                'is_featured' => true,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],
            [
                'category_slug' => 'theatre',
                'title' => 'La Traviata',
                'description' => "Giuseppe Verdi's timeless masterpiece performed by the Venice Opera Company. A breathtaking production with world-class singers and a full orchestra.",
                'slug' => 'la-traviata',
                'venue' => 'Teatro La Fenice',
                'city' => 'Venice',
                'starts_at' => now()->addDays(21)->setTime(19, 0),
                'ends_at' => now()->addDays(21)->setTime(22, 0),
                'sale_starts_at' => now()->subDays(10),
                'is_featured' => true,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],
            [
                'category_slug' => 'theatre',
                'title' => 'Opera Under the Stars',
                'description' => 'An open-air opera performance in the ancient Roman Arena of Verona. Experience the magic of live opera beneath a canopy of stars.',
                'slug' => 'opera-under-the-stars',
                'venue' => 'Arena di Verona',
                'city' => 'Verona',
                'starts_at' => now()->addDays(45)->setTime(21, 0),
                'ends_at' => now()->addDays(45)->setTime(23, 30),
                'sale_starts_at' => now()->subDays(3),
                'is_featured' => true,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],

            // Queue-enabled events
            [
                'category_slug' => 'sports',
                'title' => 'Champions League Final',
                'description' => "The biggest match of the year. Watch Europe's finest football clubs battle for the ultimate prize in club football.",
                'slug' => 'champions-league-final',
                'venue' => 'Stadio Olimpico',
                'city' => 'Rome',
                'starts_at' => now()->addDays(60)->setTime(21, 0),
                'ends_at' => now()->addDays(60)->setTime(23, 0),
                'sale_starts_at' => now()->subDays(1),
                'is_featured' => true,
                'queue_enabled' => true,
                'queue_concurrency_limit' => 5,
            ],
            [
                'category_slug' => 'festivals',
                'title' => 'Summer Beats Festival',
                'description' => 'Three days of non-stop music, food, and fun on the beautiful Neapolitan coast. Featuring over 50 artists across 4 stages.',
                'slug' => 'summer-beats-festival',
                'venue' => 'Lungomare Caracciolo',
                'city' => 'Naples',
                'starts_at' => now()->addDays(75)->setTime(14, 0),
                'ends_at' => now()->addDays(77)->setTime(2, 0),
                'sale_starts_at' => now()->subHours(12),
                'is_featured' => true,
                'queue_enabled' => true,
                'queue_concurrency_limit' => 3,
            ],
            [
                'category_slug' => 'sports',
                'title' => 'Milan Derby',
                'description' => "AC Milan vs Inter Milan. The city derby that stops Milan. Feel the passion and rivalry of one of football's greatest fixtures.",
                'slug' => 'milan-derby',
                'venue' => 'Stadio San Siro',
                'city' => 'Milan',
                'starts_at' => now()->addDays(14)->setTime(18, 0),
                'ends_at' => now()->addDays(14)->setTime(20, 0),
                'sale_starts_at' => now()->subDays(2),
                'is_featured' => false,
                'queue_enabled' => true,
                'queue_concurrency_limit' => 10,
            ],

            // Sale not started yet
            [
                'category_slug' => 'comedy',
                'title' => 'New Year Gala 2027',
                'description' => "Ring in 2027 with the biggest comedy gala of the year. Top Italian comedians perform live in an exclusive New Year's Eve celebration.",
                'slug' => 'new-year-gala-2027',
                'venue' => 'Teatro Arcimboldi',
                'city' => 'Milan',
                'starts_at' => now()->addMonths(10)->setTime(22, 0),
                'ends_at' => now()->addMonths(10)->addDay()->setTime(1, 0),
                'sale_starts_at' => now()->addDays(14),
                'is_featured' => true,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],
            [
                'category_slug' => 'conferences',
                'title' => 'AI Summit Italy 2027',
                'description' => 'The premier artificial intelligence conference in Italy. Hear from leading researchers, engineers, and entrepreneurs shaping the future.',
                'slug' => 'ai-summit-italy-2027',
                'venue' => 'Palazzo dei Congressi',
                'city' => 'Florence',
                'starts_at' => now()->addMonths(8)->setTime(9, 0),
                'ends_at' => now()->addMonths(8)->setTime(18, 0),
                'sale_starts_at' => now()->addDays(30),
                'is_featured' => false,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],

            // Non-featured, normal events
            [
                'category_slug' => 'conferences',
                'title' => 'Tech Summit 2026',
                'description' => "Join industry leaders and innovators for a full day of talks, workshops, and networking at Florence's premier tech conference.",
                'slug' => 'tech-summit-2026',
                'venue' => 'Fortezza da Basso',
                'city' => 'Florence',
                'starts_at' => now()->addDays(40)->setTime(9, 0),
                'ends_at' => now()->addDays(40)->setTime(18, 0),
                'sale_starts_at' => now()->subDays(7),
                'is_featured' => false,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],
            [
                'category_slug' => 'comedy',
                'title' => 'Stand-up Saturday',
                'description' => "A hilarious evening of stand-up comedy featuring five of Italy's funniest comedians. Prepare for non-stop laughter.",
                'slug' => 'stand-up-saturday',
                'venue' => 'Teatro Colosseo',
                'city' => 'Turin',
                'starts_at' => now()->addDays(10)->setTime(21, 0),
                'ends_at' => now()->addDays(10)->setTime(23, 0),
                'sale_starts_at' => now()->subDays(3),
                'is_featured' => false,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],
            [
                'category_slug' => 'concerts',
                'title' => 'Jazz Night in Rome',
                'description' => "An intimate evening of world-class jazz performances at one of Rome's most storied venues. Sip cocktails and let the music take you away.",
                'slug' => 'jazz-night-rome',
                'venue' => 'Auditorium Parco della Musica',
                'city' => 'Rome',
                'starts_at' => now()->addDays(18)->setTime(20, 30),
                'ends_at' => now()->addDays(18)->setTime(23, 30),
                'sale_starts_at' => now()->subDays(1),
                'is_featured' => false,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],

            // Very limited tickets (for oversell testing)
            [
                'category_slug' => 'concerts',
                'title' => 'Exclusive VIP Night',
                'description' => "An ultra-exclusive, invite-only concert experience with only a handful of tickets available. Once they're gone, they're gone.",
                'slug' => 'exclusive-vip-night',
                'venue' => 'Villa Erba',
                'city' => 'Como',
                'starts_at' => now()->addDays(25)->setTime(20, 0),
                'ends_at' => now()->addDays(25)->setTime(23, 0),
                'sale_starts_at' => now()->subHours(6),
                'is_featured' => false,
                'queue_enabled' => false,
                'queue_concurrency_limit' => 50,
            ],
        ];

        foreach ($events as $eventData) {
            $categorySlug = $eventData['category_slug'];
            unset($eventData['category_slug']);

            Event::create([
                ...$eventData,
                'category_id' => $categories->get($categorySlug)->id,
            ]);
        }
    }
}
