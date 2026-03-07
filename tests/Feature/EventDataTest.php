<?php

/**
 * Test Feature per il modello dati e i seeders del modulo 01.
 * Verifica che migrate:fresh --seed funzioni e che i dati seminati rispettino
 * le attese (categorie, eventi, tipologie biglietto, offerte, relazioni).
 * Ambiente: MySQL da .env.testing (compose mysql.test).
 */

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\VenueType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class)->beforeEach(function () {
    // RefreshDatabase ha già eseguito migrate:fresh; popoliamo con i seed
    Artisan::call('db:seed');
});

describe('Test conformità dati Eventi', function () {
    test('migrate fresh con seed esegue senza errori', function () {
        $exitCode = Artisan::call('migrate:fresh', ['--seed' => true]);

        expect($exitCode)->toBe(0);
    });

    test('i dati delle categorie hanno gli slug attesi', function () {
        $slugsAttesi = ['concerti', 'sport', 'teatro', 'cinema', 'altro'];

        $categorie = EventCategory::all();
        expect($categorie->count())->toBeGreaterThanOrEqual(4);

        $slugsPresenti = $categorie->pluck('slug')->toArray();
        foreach ($slugsAttesi as $slug) {
            expect($slugsPresenti)->toContain($slug);
        }
    });

    test('gli eventi hanno mix di featured e non featured', function () {
        $eventiFeatured = Event::where('is_featured', true)->count();
        $eventiNonFeatured = Event::where('is_featured', false)->count();

        expect($eventiFeatured)->toBeGreaterThan(0);
        expect($eventiNonFeatured)->toBeGreaterThan(0);
    });

    test('almeno un evento ha prevendita non ancora iniziata', function () {
        $ora = now();
        $eventiConVenditaFutura = Event::where('sale_starts_at', '>', $ora)->count();

        expect($eventiConVenditaFutura)->toBeGreaterThan(0);
    });

    test('ogni evento ha tipologie di biglietto con venue type coerente', function () {
        $eventi = Event::with(['ticketTypes.venueType', 'venueType'])->get();
        expect($eventi->count())->toBeGreaterThan(0);

        foreach ($eventi as $evento) {
            $tipologie = $evento->ticketTypes;
            expect($tipologie->count())->toBeGreaterThanOrEqual(1);

            foreach ($tipologie as $ticketType) {
                expect($ticketType->venue_type_id)->toBe($evento->venue_type_id);
            }
        }
    });

    test('ogni tipologia biglietto ha almeno un offerta ticket', function () {
        $tipologie = TicketType::withCount('tickets')->get();
        expect($tipologie->count())->toBeGreaterThan(0);

        foreach ($tipologie as $ticketType) {
            expect($ticketType->tickets_count)->toBeGreaterThanOrEqual(1);
        }
    });

    test('la relazione evento categoria e corretta', function () {
        $evento = Event::with('category')->first();
        expect($evento)->not->toBeNull();
        expect($evento->category)->not->toBeNull();
        expect($evento->event_category_id)->toBe($evento->category->id);
    });

    test('la relazione evento venue type e corretta', function () {
        $evento = Event::with('venueType')->first();
        expect($evento)->not->toBeNull();
        expect($evento->venueType)->not->toBeNull();
        expect($evento->venue_type_id)->toBe($evento->venueType->id);
    });

    test('la relazione ticket type evento e corretta', function () {
        $ticketType = TicketType::with('event')->first();
        expect($ticketType)->not->toBeNull();
        expect($ticketType->event)->not->toBeNull();
        expect($ticketType->event_id)->toBe($ticketType->event->id);
    });

    test('la relazione ticket type venue type e corretta', function () {
        $ticketType = TicketType::with('venueType')->first();
        expect($ticketType)->not->toBeNull();
        expect($ticketType->venueType)->not->toBeNull();
        expect($ticketType->venue_type_id)->toBe($ticketType->venueType->id);
    });

    test('la relazione ticket ticket type e corretta', function () {
        $ticket = Ticket::with('ticketType')->first();
        expect($ticket)->not->toBeNull();
        expect($ticket->ticketType)->not->toBeNull();
        expect($ticket->ticket_type_id)->toBe($ticket->ticketType->id);
    });
});
