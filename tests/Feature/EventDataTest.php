<?php

/**
 * Test Feature per il modello dati e i seeders del modulo 01.
 * Verifica che migrate:fresh --seed funzioni e che i dati seminati rispettino
 * le attese (categorie, eventi, tipologie biglietto, offerte, relazioni).
 * Ambiente: MySQL da .env.testing (compose mysql.test).
 */

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\TicketType;
use App\Models\TicketTypeQuota;
use App\Models\Ticket;
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

describe('Quote per tipologia (TicketTypeQuota) e Observer', function () {
    test('dopo il seed la somma delle quote per evento non supera available_tickets', function () {
        $eventi = Event::where('available_tickets', '>', 0)->with('ticketTypes')->get();
        expect($eventi->count())->toBeGreaterThan(0);

        foreach ($eventi as $evento) {
            $ticketTypes = $evento->ticketTypes;
            if ($ticketTypes->isEmpty()) {
                continue;
            }
            $sommaQuote = TicketTypeQuota::whereIn('ticket_type_id', $ticketTypes->pluck('id'))->sum('quantity');
            expect($sommaQuote)->toBeLessThanOrEqual((int) $evento->available_tickets);
        }
    });

    test('ogni ticket type di eventi con available_tickets ha una quota dopo il seed', function () {
        $eventi = Event::where('available_tickets', '>', 0)->with('ticketTypes')->get();
        foreach ($eventi as $evento) {
            foreach ($evento->ticketTypes as $ticketType) {
                $quota = TicketTypeQuota::where('ticket_type_id', $ticketType->id)->first();
                expect($quota)->not->toBeNull();
                expect($quota->quantity)->toBeGreaterThan(0);
            }
        }
    });

    test('creare una quota che supera available_tickets solleva InvalidArgumentException', function () {
        $evento = Event::where('available_tickets', '>', 0)->with('ticketTypes')->first();
        expect($evento)->not->toBeNull();
        $giaAssegnato = TicketTypeQuota::whereIn(
            'ticket_type_id',
            $evento->ticketTypes->pluck('id')
        )->sum('quantity');
        $totale = (int) $evento->available_tickets;
        expect($giaAssegnato)->toBeLessThanOrEqual($totale);

        $nuovoTipo = TicketType::factory()->create([
            'event_id' => $evento->id,
            'venue_type_id' => $evento->venue_type_id,
            'name' => 'Extra',
        ]);
        $quantitaEccedente = $totale - $giaAssegnato + 1;

        TicketTypeQuota::create([
            'ticket_type_id' => $nuovoTipo->id,
            'quantity' => $quantitaEccedente,
        ]);
    })->throws(\InvalidArgumentException::class);

    test('aggiornare una quota oltre available_tickets solleva InvalidArgumentException', function () {
        $evento = Event::where('available_tickets', '>', 0)->with('ticketTypes')->first();
        expect($evento)->not->toBeNull();
        $quota = TicketTypeQuota::whereIn(
            'ticket_type_id',
            $evento->ticketTypes->pluck('id')
        )->first();
        expect($quota)->not->toBeNull();

        $totale = (int) $evento->available_tickets;
        $altreQuote = TicketTypeQuota::whereIn('ticket_type_id', $evento->ticketTypes->pluck('id'))
            ->where('id', '!=', $quota->id)
            ->sum('quantity');
        $nuovaQty = $totale - $altreQuote + 1;

        $quota->update(['quantity' => $nuovaQty]);
    })->throws(\InvalidArgumentException::class);

    test('creare e aggiornare quote entro available_tickets non solleva eccezione', function () {
        $startsAt = now()->addWeeks(2);
        $evento = Event::factory()->create([
            'title' => 'Evento Test Quote',
            'description' => 'Descrizione test',
            'location' => 'Roma',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHours(2),
            'sale_starts_at' => $startsAt->copy()->subDays(7),
            'available_tickets' => 100,
            'event_category_id' => EventCategory::first()->id,
            'venue_type_id' => VenueType::first()->id,
        ]);
        $tt1 = TicketType::factory()->create(['event_id' => $evento->id, 'venue_type_id' => $evento->venue_type_id, 'name' => 'Tipo A']);
        $tt2 = TicketType::factory()->create(['event_id' => $evento->id, 'venue_type_id' => $evento->venue_type_id, 'name' => 'Tipo B']);

        TicketTypeQuota::create(['ticket_type_id' => $tt1->id, 'quantity' => 50]);
        TicketTypeQuota::create(['ticket_type_id' => $tt2->id, 'quantity' => 50]);

        $q1 = TicketTypeQuota::where('ticket_type_id', $tt1->id)->first();
        $q1->update(['quantity' => 49]);
        TicketTypeQuota::where('ticket_type_id', $tt2->id)->first()->update(['quantity' => 51]);

        $somma = (int) TicketTypeQuota::whereIn('ticket_type_id', [$tt1->id, $tt2->id])->sum('quantity');
        expect($somma)->toBe(100);
    });
});
