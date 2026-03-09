<?php

use Carbon\CarbonImmutable;
use App\Enums\QueueEntryStatus;
use App\Jobs\ProcessQueueJob;
use App\Models\Event;
use App\Models\EventCategory;
use App\Enums\OrderStatusEnum;
use App\Models\Hold;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\QueueEntry;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\TicketTypeQuota;
use App\Models\User;
use App\Models\VenueType;
use App\Services\QueueService;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function createQueueTicketForTest(array $eventOverrides = [], array $ticketOverrides = [], array $queueConfig = []): array
{
    $slug = Str::random(12);
    $category = EventCategory::create([
        'name' => 'Concerti',
        'slug' => 'concerti-' . $slug,
    ]);

    $venueType = VenueType::create([
        'name' => 'Arena',
        'slug' => 'arena-' . $slug,
    ]);

    $event = Event::query()->create(array_merge([
        'title' => 'Evento Coda Test',
        'description' => 'Evento per verificare coda',
        'event_category_id' => $category->id,
        'venue_type_id' => $venueType->id,
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10)->addHours(2),
        'sale_starts_at' => now()->subDay(),
        'location' => 'Roma',
        'image_url' => null,
        'is_featured' => false,
        'is_active' => true,
        'available_tickets' => 50,
        'queue_enabled' => true,
        'queue_config' => array_merge([
            'max_concurrent' => 1,
            'duration_minutes' => 10,
        ], $queueConfig),
    ], $eventOverrides));

    $ticketType = TicketType::query()->create([
        'event_id' => $event->id,
        'venue_type_id' => $venueType->id,
        'name' => 'Standard',
    ]);

    $ticketTypeQuantity = array_key_exists('quota_quantity', $ticketOverrides)
        ? $ticketOverrides['quota_quantity']
        : 50;
    unset($ticketOverrides['quota_quantity']);

    TicketTypeQuota::query()->create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => $ticketTypeQuantity,
    ]);

    $ticket = Ticket::query()->create(array_merge([
        'ticket_type_id' => $ticketType->id,
        'price' => '49.90',
        'max_per_user' => 4,
    ], $ticketOverrides));

    return compact('event', 'ticketType', 'ticket');
}

function assertQueueEntryEnabledForUser(Event $event, User $user): void
{
    actingAs($user);
    $queueStatus = get("/events/{$event->id}/queue/status")->inertiaProps('queueStatus');
    expect($queueStatus['status'])->toBe(QueueEntryStatus::ENABLED->value);
}

function getQueueStatusFromPage(Event $event): array
{
    return get("/events/{$event->id}/queue/status")->inertiaProps('queueStatus');
}

function getQueueEventFromPage(Event $event): array
{
    return get("/events/{$event->id}/queue/status")->inertiaProps('event');
}

function joinEventQueue(Event $event): void
{
    post("/events/{$event->id}/queue/join")->assertRedirect("/events/{$event->slug}");
}

describe('Coda eventi', function (): void {
    test('POST /events/{id}/queue/join registra lo stato corretto', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createQueueTicketForTest();

        actingAs($user);

        joinEventQueue($event);
        $statusResponse = get("/events/{$event->id}/queue/status");
        $statusResponse->assertOk();
        $statusResponse->assertInertia(fn (Assert $page) => $page
            ->where('queueStatus.status', QueueEntryStatus::ENABLED->value)
        );

        $entry = QueueEntry::query()
            ->where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->latest('id')
            ->first();

        expect($entry)->not->toBeNull();
        expect($entry->status)->toBe(QueueEntryStatus::ENABLED);
        expect($ticket->fresh()->getAvailableQuantity())->toBe(50);
    });

    test('tempo stimato in coda tiene conto del batch in base a max_concurrent', function (): void {
        $previousTestNow = CarbonImmutable::getTestNow();
        CarbonImmutable::setTestNow(now()->startOfMinute());

        try {
        ['event' => $event] = createQueueTicketForTest(queueConfig: [
            'max_concurrent' => 2,
            'duration_minutes' => 10,
        ]);

        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $thirdUser = User::factory()->create();
        $fourthUser = User::factory()->create();

        actingAs($firstUser);
        joinEventQueue($event);

        actingAs($secondUser);
        joinEventQueue($event);

        actingAs($thirdUser);
        joinEventQueue($event);
        $thirdStatus = getQueueStatusFromPage($event);
        expect((string) $thirdStatus['status'])->toBe(QueueEntryStatus::WAITING->value);
        expect((int) $thirdStatus['position'])->toBe(1);
        expect((int) $thirdStatus['estimated_wait_seconds'])->toBe(600);

        actingAs($fourthUser);
        joinEventQueue($event);
        $fourthStatus = getQueueStatusFromPage($event);
        expect((string) $fourthStatus['status'])->toBe(QueueEntryStatus::WAITING->value);
        expect((int) $fourthStatus['position'])->toBe(2);
        expect((int) $fourthStatus['estimated_wait_seconds'])->toBe(600);
        } finally {
            CarbonImmutable::setTestNow($previousTestNow);
        }
    });

    test('tempo stimato in coda considera le scadenze degli slot abilitati', function (): void {
        $previousTestNow = CarbonImmutable::getTestNow();
        CarbonImmutable::setTestNow(now()->startOfMinute());

        try {
            ['event' => $event] = createQueueTicketForTest(queueConfig: [
                'max_concurrent' => 2,
                'duration_minutes' => 10,
            ]);

            $firstUser = User::factory()->create();
            $secondUser = User::factory()->create();
            $thirdUser = User::factory()->create();
            $fourthUser = User::factory()->create();

            actingAs($firstUser);
            joinEventQueue($event);

            actingAs($secondUser);
            joinEventQueue($event);

            $firstEntry = QueueEntry::query()
                ->where('user_id', $firstUser->id)
                ->where('event_id', $event->id)
                ->latest('id')
                ->firstOrFail();
            $secondEntry = QueueEntry::query()
                ->where('user_id', $secondUser->id)
                ->where('event_id', $event->id)
                ->latest('id')
                ->firstOrFail();

            $baseNow = CarbonImmutable::now();
            $firstEntry->update([
                'enabled_until' => $baseNow->addMinutes(1),
                'enabled_at' => $baseNow,
            ]);
            $secondEntry->update([
                'enabled_until' => $baseNow->addMinutes(13),
                'enabled_at' => $baseNow,
            ]);

            actingAs($thirdUser);
            joinEventQueue($event);
            $thirdStatus = getQueueStatusFromPage($event);
            expect((string) $thirdStatus['status'])->toBe(QueueEntryStatus::WAITING->value);
            expect((int) $thirdStatus['position'])->toBe(1);
            expect((int) $thirdStatus['estimated_wait_seconds'])->toBe(60);

            actingAs($fourthUser);
            joinEventQueue($event);
            $fourthStatus = getQueueStatusFromPage($event);
            expect((string) $fourthStatus['status'])->toBe(QueueEntryStatus::WAITING->value);
            expect((int) $fourthStatus['position'])->toBe(2);
            expect((int) $fourthStatus['estimated_wait_seconds'])->toBe(660);
        } finally {
            CarbonImmutable::setTestNow($previousTestNow);
        }
    });

    test('POST /cart/hold in waiting viene bloccato', function (): void {
        $eventUser = User::factory()->create();
        $waitingUser = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createQueueTicketForTest();

        actingAs($eventUser);
        joinEventQueue($event);

        actingAs($waitingUser);
        joinEventQueue($event);
        $response = from("/events/{$event->slug}")->post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ]);

        $response
            ->assertRedirect("/events/{$event->slug}")
            ->assertSessionHasErrors('queue');
        expect(QueueEntry::query()->where('user_id', $waitingUser->id)->where('event_id', $event->id)->first()?->status)->toBe(QueueEntryStatus::WAITING);
        expect(Hold::query()->where('user_id', $waitingUser->id)->count())->toBe(0);
    });

    test('POST /cart/hold in enabled passa', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createQueueTicketForTest();

        actingAs($user);
        joinEventQueue($event);
        assertQueueEntryEnabledForUser($event, $user);
        from("/events/{$event->slug}")->post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ])->assertRedirect("/events/{$event->slug}");

        expect(Hold::query()->where('user_id', $user->id)->count())->toBe(1);
    });

    test('enabled_until segue l\'ultimo hold attivo dell\'utente su quell\'evento', function (): void {
        $user = User::factory()->create();
        ['ticket' => $firstTicket, 'ticketType' => $firstTicketType, 'event' => $event] = createQueueTicketForTest(
            ticketOverrides: ['quota_quantity' => 30],
        );

        $secondTicketType = TicketType::query()->create([
            'event_id' => $event->id,
            'venue_type_id' => $firstTicketType->venue_type_id,
            'name' => 'VIP',
        ]);
        TicketTypeQuota::query()->create([
            'ticket_type_id' => $secondTicketType->id,
            'quantity' => 20,
        ]);
        $secondTicket = Ticket::query()->create([
            'ticket_type_id' => $secondTicketType->id,
            'price' => '79.90',
            'max_per_user' => 4,
        ]);

        actingAs($user);
        joinEventQueue($event);
        assertQueueEntryEnabledForUser($event, $user);
        from("/events/{$event->slug}")->post('/cart/hold', [
            'ticket_id' => $firstTicket->id,
            'quantity' => 1,
        ])->assertRedirect("/events/{$event->slug}");

        from("/events/{$event->slug}")->post('/cart/hold', [
            'ticket_id' => $secondTicket->id,
            'quantity' => 1,
        ])->assertRedirect("/events/{$event->slug}");

        $lastHold = Hold::query()
            ->where('user_id', $user->id)
            ->whereIn('ticket_id', [$firstTicket->id, $secondTicket->id])
            ->latest('updated_at')
            ->firstOrFail();
        $entry = QueueEntry::query()
            ->where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', QueueEntryStatus::ENABLED->value)
            ->firstOrFail();

        expect($entry->enabled_until)->not->toBeNull();
        expect($entry->enabled_until?->getTimestamp())->toBe($lastHold->expires_at?->getTimestamp());
    });

    test('GET /events/{id}/queue/status ritorna stato e posizione', function (): void {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        ['event' => $event] = createQueueTicketForTest();

        actingAs($firstUser);
        joinEventQueue($event);

        actingAs($secondUser);
        joinEventQueue($event);
        $response = get("/events/{$event->id}/queue/status");

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('queueStatus.status', QueueEntryStatus::WAITING->value)
            ->where('queueStatus.position', 1)
        );
    });

    test('GET /events/{id}/queue/status ritorna anche i dati aggiornati dell\'evento', function (): void {
        $holdUser = User::factory()->create();
        $viewerUser = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createQueueTicketForTest();

        actingAs($holdUser);
        joinEventQueue($event);
        assertQueueEntryEnabledForUser($event, $holdUser);
        from("/events/{$event->slug}")->post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ])->assertRedirect("/events/{$event->slug}");

        $hold = Hold::query()->where('user_id', $holdUser->id)->latest('id')->firstOrFail();

        actingAs($viewerUser);
        $before = getQueueEventFromPage($event);
        expect($before['id'])->toBe($event->id);
        expect($before['ticket_types'][0]['tickets'][0]['available_quantity'])->toBe(49);

        actingAs($holdUser);
        from('/cart')->delete("/cart/hold/{$hold->id}")->assertRedirect('/cart');

        actingAs($viewerUser);
        $after = getQueueEventFromPage($event);
        expect($after['ticket_types'][0]['tickets'][0]['available_quantity'])->toBe(50);
    });

    test('ProcessQueueJob promuove waiting rispettando FIFO', function (): void {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $thirdUser = User::factory()->create();
        ['event' => $event] = createQueueTicketForTest(queueConfig: ['max_concurrent' => 1]);

        actingAs($firstUser);
        joinEventQueue($event);

        actingAs($secondUser);
        joinEventQueue($event);

        actingAs($thirdUser);
        joinEventQueue($event);

        $firstEntry = QueueEntry::query()
            ->where('user_id', $firstUser->id)
            ->where('event_id', $event->id)
            ->firstOrFail();
        $secondEntry = QueueEntry::query()
            ->where('user_id', $secondUser->id)
            ->where('event_id', $event->id)
            ->firstOrFail();
        $thirdEntry = QueueEntry::query()
            ->where('user_id', $thirdUser->id)
            ->where('event_id', $event->id)
            ->firstOrFail();

        $firstEntry->update(['enabled_until' => now()->subMinute()]);

        (new ProcessQueueJob())->handle(app(QueueService::class));

        expect($secondEntry->fresh()?->status)->toBe(QueueEntryStatus::ENABLED);
        expect($thirdEntry->fresh()?->status)->toBe(QueueEntryStatus::WAITING);
        expect(QueueEntry::query()->where('status', QueueEntryStatus::EXPIRED)->count())->toBe(1);
    });

    test('checkout con slot valido marca queue entry come completed', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createQueueTicketForTest();

        actingAs($user);
        joinEventQueue($event);
        assertQueueEntryEnabledForUser($event, $user);
        from("/events/{$event->slug}")->post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ])->assertRedirect("/events/{$event->slug}");

        post('/checkout')->assertRedirect();

        $order = Order::query()->where('user_id', $user->id)->latest('id')->firstOrFail();
        $orderItem = OrderItem::query()->where('order_id', $order->id)->firstOrFail();
        expect((float) $order->total_amount)->toBe(49.90);
        expect($order->status)->toBe(OrderStatusEnum::COMPLETED);
        expect($orderItem->unit_price)->toBe('49.90');

        $entry = QueueEntry::query()
            ->where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', QueueEntryStatus::COMPLETED)
            ->firstOrFail();
        expect((int) $entry->id)->toBeGreaterThan(0);

        $statusResponse = get("/events/{$event->id}/queue/status");
        $statusResponse->assertOk();
        $statusResponse->assertInertia(fn (Assert $page) => $page
            ->where('queueStatus.status', null)
        );
    });

    test('checkout con slot scaduto rifiuta la richiesta', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket, 'event' => $event] = createQueueTicketForTest();

        actingAs($user);
        joinEventQueue($event);
        assertQueueEntryEnabledForUser($event, $user);
        from("/events/{$event->slug}")->post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ])->assertRedirect("/events/{$event->slug}");

        QueueEntry::query()
            ->where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->update([
                'status' => QueueEntryStatus::ENABLED->value,
                'enabled_until' => now()->subMinute(),
            ]);

        $response = from('/checkout')->post('/checkout');

        $response->assertRedirect('/checkout')->assertSessionHasErrors('queue');
        expect(Order::query()->count())->toBe(0);
    });
});
