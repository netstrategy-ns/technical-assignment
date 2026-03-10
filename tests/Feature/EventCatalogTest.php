<?php

/**
 * Test Feature per il catalogo eventi pubblico (modulo 02) e ricerca/filtri (modulo 03).
 * GET home, GET /events, GET /events/{slug}, 404, e filtri (search, category, date, location, sort).
 */

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\VenueType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;

uses(RefreshDatabase::class)->beforeEach(function (): void {
    Artisan::call('db:seed');
});

function createEventForTest(array $overrides = []): Event
{
    $category = EventCategory::first();
    $venueType = VenueType::first();
    $startsAt = $overrides['starts_at'] ?? now()->addWeeks(2);
    $endsAt = $overrides['ends_at'] ?? (is_object($startsAt) ? $startsAt->copy()->addHours(2) : \Carbon\Carbon::parse($startsAt)->addHours(2));

    return Event::factory()->create(array_merge([
        'title' => 'Evento Test',
        'description' => 'Descrizione test',
        'event_category_id' => $category->id,
        'venue_type_id' => $venueType->id,
        'location' => 'Roma',
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'sale_starts_at' => is_object($startsAt) ? $startsAt->copy()->subDays(7) : \Carbon\Carbon::parse($startsAt)->subDays(7),
        'is_featured' => false,
        'is_active' => true,
        'available_tickets' => 100,
    ], $overrides));
}

describe('Catalogo eventi', function (): void {
    test('GET / ritorna 200 e props con eventi in evidenza', function (): void {
        $response = get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/home/Index')
            ->has('featuredEvents')
            ->has('canRegister')
        );
    });

    test('GET /events ritorna 200 e props con eventi paginati', function (): void {
        $response = get('/events');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Index')
            ->has('events')
            ->has('events.data')
            ->has('filters')
        );
    });

    test('GET /events?featured=1 filtra eventi in evidenza', function (): void {
        $response = get('/events?featured=1');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Index')
            ->where('filters.featured', true)
        );
    });

    test('GET /events/{slug} ritorna 200 con evento, ticketTypes, disponibili e saleNotStarted', function (): void {
        $event = createEventForTest(['title' => 'Evento Dettaglio Test']);
        $event->load(['ticketTypes.tickets', 'ticketTypes.quota']);

        $response = get('/events/'.$event->slug);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Show')
            ->has('event')
            ->has('saleNotStarted')
        );

        $eventProps = $response->inertiaProps('event');
        expect($eventProps)->toBeArray();
        // JsonResource può essere serializzato come { data: {...} }; estraiamo l'array interno
        if (isset($eventProps['data']) && is_array($eventProps['data']) && array_key_exists('id', $eventProps['data'])) {
            $eventProps = $eventProps['data'];
        }
        expect($eventProps)->toHaveKey('slug');
        expect($eventProps['slug'])->toBe($event->slug);

        $raw = $eventProps['ticket_types'] ?? [];
        $ticketTypes = (isset($raw['data']) && is_array($raw['data'])) ? $raw['data'] : $raw;
        expect($ticketTypes)->toBeArray();
        foreach ($ticketTypes as $tt) {
            expect($tt)->toHaveKeys(['available_quantity', 'quota_quantity', 'tickets']);
        }
    });

    test('GET /events/slug-inesistente ritorna 404', function (): void {
        $response = get('/events/slug-inesistente-12345');

        $response->assertNotFound();
    });
});

describe('Ricerca e filtri eventi', function (): void {
    test('GET /events?search=... restituisce eventi filtrati per titolo', function (): void {
        $event = createEventForTest(['title' => 'Concerto Rock Unico Cercabile']);
        $other = createEventForTest(['title' => 'Festival Jazz Altro']);

        $response = get('/events?search=Rock+Unico');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Index')
            ->has('events.data')
            ->where('filters.search', 'Rock Unico')
        );
        $data = $response->inertiaProps('events')['data'];
        expect($data)->not->toBeEmpty();
        $ids = array_column($data, 'id');
        expect($ids)->toContain($event->id);
        expect($ids)->not->toContain($other->id);
        foreach ($data as $item) {
            expect(stripos($item['title'], 'Rock') !== false || stripos($item['title'], 'Unico') !== false)->toBeTrue();
        }
    });

    test('GET /events?category=... restituisce solo eventi della categoria', function (): void {
        $category = EventCategory::first();
        expect($category)->not->toBeNull();
        $eventInCategory = createEventForTest(['event_category_id' => $category->id]);
        $otherCategory = EventCategory::where('id', '!=', $category->id)->first();
        $eventOther = $otherCategory ? createEventForTest(['event_category_id' => $otherCategory->id]) : null;

        $response = get('/events?category='.$category->slug);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Index')
            ->has('events.data')
            ->where('filters.category', $category->slug)
        );
        $data = $response->inertiaProps('events')['data'];
        foreach ($data as $item) {
            expect(($item['category'] ?? [])['slug'] ?? null)->toBe($category->slug);
        }
        $ids = array_column($data, 'id');
        expect($ids)->toContain($eventInCategory->id);
        if ($eventOther !== null) {
            expect($ids)->not->toContain($eventOther->id);
        }
    });

    test('GET /events?start_date=...&end_date=... restituisce eventi nel range', function (): void {
        $inRange = createEventForTest([
            'starts_at' => '2025-06-15 20:00:00',
            'ends_at' => '2025-06-15 22:00:00',
        ]);
        $outOfRange = createEventForTest([
            'starts_at' => '2025-08-10 20:00:00',
            'ends_at' => '2025-08-10 22:00:00',
        ]);

        $response = get('/events?start_date=2025-06-01&end_date=2025-06-30');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Index')
            ->has('events.data')
        );
        $filters = $response->inertiaProps('filters');
        expect($filters['start_date'])->toContain('2025-06-01');
        expect($filters['end_date'])->toContain('2025-06-30');
        $data = $response->inertiaProps('events')['data'];
        $ids = array_column($data, 'id');
        expect($ids)->toContain($inRange->id);
        expect($ids)->not->toContain($outOfRange->id);
    });

    test('GET /events?location=... filtra per luogo', function (): void {
        $event = createEventForTest(['location' => 'Milano Via Filtro Test 1']);
        $other = createEventForTest(['location' => 'Roma Via Altra']);

        $response = get('/events?location=Filtro+Test');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Index')
            ->has('events.data')
            ->where('filters.location', 'Filtro Test')
        );
        $data = $response->inertiaProps('events')['data'];
        $ids = array_column($data, 'id');
        expect($ids)->toContain($event->id);
        expect($ids)->not->toContain($other->id);
        foreach ($data as $item) {
            expect(stripos($item['location'] ?? '', 'Filtro') !== false)->toBeTrue();
        }
    });

    test('GET /events?sort=date_asc restituisce ordine data crescente', function (): void {
        $response = get('/events?sort=date_asc&per_page=5');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Index')
            ->where('filters.sort', 'date_asc')
        );
        $data = $response->inertiaProps('events')['data'];
        if (count($data) >= 2) {
            $first = $data[0]['starts_at'] ?? null;
            $second = $data[1]['starts_at'] ?? null;
            if ($first && $second) {
                expect(strtotime($first))->toBeLessThanOrEqual(strtotime($second));
            }
        }
    });

    test('GET /events?sort=date_desc restituisce ordine data decrescente', function (): void {
        $response = get('/events?sort=date_desc&per_page=5');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'date_desc')
        );
        $data = $response->inertiaProps('events')['data'];
        if (count($data) >= 2) {
            $first = $data[0]['starts_at'] ?? null;
            $second = $data[1]['starts_at'] ?? null;
            if ($first && $second) {
                expect(strtotime($first))->toBeGreaterThanOrEqual(strtotime($second));
            }
        }
    });

    test('GET /events?sort=featured_first restituisce in evidenza prima', function (): void {
        $response = get('/events?sort=featured_first&per_page=10');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'featured_first')
        );
        $data = $response->inertiaProps('events')['data'];
        $featuredIndices = [];
        foreach ($data as $i => $item) {
            if (! empty($item['is_featured'])) {
                $featuredIndices[] = $i;
            }
        }
        $nonFeaturedIndices = array_keys(array_filter($data, fn ($item) => empty($item['is_featured'])));
        if (count($featuredIndices) > 0 && count($nonFeaturedIndices) > 0) {
            expect(max($featuredIndices))->toBeLessThan(min($nonFeaturedIndices));
        }
    });

    test('combinazione filtri e risultati vuoti restituisce 200 e lista vuota', function (): void {
        $response = get('/events?search=NonEsisteMai12345XYZ&category=slug-inesistente');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/events/Index')
            ->has('events.data')
            ->has('filters')
        );
        $data = $response->inertiaProps('events')['data'];
        expect($data)->toBeArray();
        expect($data)->toBeEmpty();
    });
});
