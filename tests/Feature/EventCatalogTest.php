<?php

/**
 * Test Feature per il catalogo eventi pubblico (modulo 02).
 * GET home, GET /events, GET /events/{slug} e pagina 404.
 */

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;

uses(RefreshDatabase::class)->beforeEach(function (): void {
    Artisan::call('db:seed');
});

describe('Catalogo eventi (pubblico)', function (): void {
    test('GET / ritorna 200 e props con eventi in evidenza', function (): void {
        $response = get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('frontend/home/Index')
            ->has('featuredEvents')
            ->has('canRegister')
        );
    });

    test('GET /events ritorna 200 e props con eventi paginati', function (): void {
        $response = get('/events');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('frontend/events/Index')
            ->has('events')
            ->has('events.data')
            ->has('filters')
        );
    });

    test('GET /events?featured=1 filtra eventi in evidenza', function (): void {
        $response = get('/events?featured=1');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('frontend/events/Index')
            ->where('filters.featured', true)
        );
    });

    test('GET /events/{slug} ritorna 200 con evento, ticketTypes, disponibili e saleNotStarted', function (): void {
        $event = Event::whereNotNull('slug')->with('ticketTypes')->first();
        expect($event)->not->toBeNull();

        $response = get('/events/'.$event->slug);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('frontend/events/Show')
            ->has('event')
            ->has('event.ticket_types')
            ->has('event.slug')
            ->where('event.slug', $event->slug)
            ->has('saleNotStarted')
        );

        $eventProps = $response->inertiaProps('event');
        expect($eventProps['ticket_types'])->toBeArray();
        foreach ($eventProps['ticket_types'] as $tt) {
            expect($tt)->toHaveKeys(['available_quantity', 'quota_quantity', 'tickets']);
        }
    });

    test('GET /events/slug-inesistente ritorna 404', function (): void {
        $response = get('/events/slug-inesistente-12345');

        $response->assertNotFound();
    });
});
