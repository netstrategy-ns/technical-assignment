<?php

use App\Enums\HoldStatusEnum;
use App\Jobs\ExpireHoldsJob;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Hold;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\TicketTypeQuota;
use App\Models\User;
use App\Models\VenueType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createTicketForExpireHoldTest(array $eventOverrides = [], array $ticketOverrides = []): array
{
    $category = EventCategory::create([
        'name' => 'Concerti',
        'slug' => 'concerti',
    ]);

    $venueType = VenueType::create([
        'name' => 'Arena',
        'slug' => 'arena',
    ]);

    $event = Event::create(array_merge([
        'title' => 'Evento Expire Hold Test',
        'description' => 'Descrizione evento expire hold test',
        'event_category_id' => $category->id,
        'venue_type_id' => $venueType->id,
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10)->addHours(2),
        'sale_starts_at' => now()->subDay(),
        'location' => 'Roma',
        'image_url' => null,
        'is_featured' => false,
        'is_active' => true,
        'available_tickets' => 10,
    ], $eventOverrides));

    $ticketType = TicketType::create([
        'event_id' => $event->id,
        'venue_type_id' => $venueType->id,
        'name' => 'Standard',
    ]);

    TicketTypeQuota::create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => 10,
    ]);

    $ticket = Ticket::create(array_merge([
        'ticket_type_id' => $ticketType->id,
        'price' => '49.90',
        'quantity_total' => 10,
        'max_per_user' => 10,
    ], $ticketOverrides));

    return compact('event', 'ticketType', 'ticket');
}

describe('Expire holds command', function (): void {
    test('segna come expired le hold attive con expires_at gia superata', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createTicketForExpireHoldTest();

        $expiredHold = Hold::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'expires_at' => now()->subMinute(),
            'status' => HoldStatusEnum::ACTIVE,
        ]);

        $futureHold = Hold::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'expires_at' => now()->addMinutes(5),
            'status' => HoldStatusEnum::ACTIVE,
        ]);

        artisan('expire-holds')
            ->expectsOutput('Hold scadute aggiornate: 1.')
            ->assertSuccessful();

        expect($expiredHold->fresh()->status)->toBe(HoldStatusEnum::EXPIRED);
        expect($futureHold->fresh()->status)->toBe(HoldStatusEnum::ACTIVE);
    });

    test('ripristina la disponibilita del ticket dopo la scadenza', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createTicketForExpireHoldTest([], [
            'quantity_total' => 10,
        ]);

        Hold::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => 4,
            'expires_at' => now()->subMinute(),
            'status' => HoldStatusEnum::ACTIVE,
        ]);

        expect($ticket->fresh()->getAvailableQuantity())->toBe(10);

        artisan('expire-holds')->assertSuccessful();

        expect($ticket->fresh()->getAvailableQuantity())->toBe(10);
        expect(Hold::query()->firstOrFail()->status)->toBe(HoldStatusEnum::EXPIRED);
    });

    test('aggiorna tutte le hold scadute anche in batch piccoli', function (): void {
        ['ticket' => $ticket] = createTicketForExpireHoldTest([], [
            'quantity_total' => 20,
        ]);

        $users = User::factory()->count(4)->create();

        foreach ($users->take(3) as $user) {
            Hold::create([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'quantity' => 1,
                'expires_at' => now()->subMinute(),
                'status' => HoldStatusEnum::ACTIVE,
            ]);
        }

        Hold::create([
            'user_id' => $users->last()->id,
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'expires_at' => now()->addMinutes(5),
            'status' => HoldStatusEnum::ACTIVE,
        ]);

        artisan('expire-holds', ['--chunk' => 1])
            ->expectsOutput('Hold scadute aggiornate: 3.')
            ->assertSuccessful();

        expect(Hold::query()->where('status', HoldStatusEnum::EXPIRED->value)->count())->toBe(3);
        expect(Hold::query()->where('status', HoldStatusEnum::ACTIVE->value)->count())->toBe(1);
    });
});

describe('Expire holds job', function (): void {
    test('esegue la stessa scadenza automatica del comando', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createTicketForExpireHoldTest();

        $expiredHold = Hold::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'expires_at' => now()->subMinute(),
            'status' => HoldStatusEnum::ACTIVE,
        ]);

        ExpireHoldsJob::dispatchSync();

        expect($expiredHold->fresh()->status)->toBe(HoldStatusEnum::EXPIRED);
    });
});

describe('Expired holds and cart payload', function (): void {
    test('GET /cart non restituisce hold con expires_at gia passata', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createTicketForExpireHoldTest();

        Hold::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'expires_at' => now()->subMinute(),
            'status' => HoldStatusEnum::ACTIVE,
        ]);

        actingAs($user);

        $response = get('/cart');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('frontend/cart/Index')
            ->has('cart.items', 0)
        );

        $eventResponse = get('/events/'.$event->slug);

        $eventResponse->assertOk();
        $eventResponse->assertInertia(fn (Assert $page) => $page
            ->where('event.ticket_types.0.available_quantity', 10)
            ->where('event.ticket_types.0.tickets.0.available_quantity', 10)
            ->where('event.ticket_types.0.tickets.0.user_hold_quantity', 0)
        );
    });
});
