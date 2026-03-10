<?php

use App\Enums\HoldStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Hold;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\TicketTypeQuota;
use App\Models\User;
use App\Models\VenueType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function createCheckoutTicketForTest(array $eventOverrides = [], array $ticketOverrides = []): array
{
    $category = EventCategory::create([
        'name' => 'Concerti',
        'slug' => 'concerti-' . fake()->unique()->randomNumber(5),
    ]);

    $venueType = VenueType::create([
        'name' => 'Arena',
        'slug' => 'arena-' . fake()->unique()->randomNumber(5),
    ]);

    $event = Event::create(array_merge([
        'title' => 'Evento Checkout Test',
        'description' => 'Descrizione evento checkout test',
        'event_category_id' => $category->id,
        'venue_type_id' => $venueType->id,
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10)->addHours(2),
        'sale_starts_at' => now()->subDay(),
        'location' => 'Roma',
        'image_url' => null,
        'is_featured' => false,
        'is_active' => true,
        'available_tickets' => 20,
    ], $eventOverrides));

    $ticketType = TicketType::create([
        'event_id' => $event->id,
        'venue_type_id' => $venueType->id,
        'name' => 'Standard',
    ]);

    $ticketTypeQuantity = array_key_exists('quota_quantity', $ticketOverrides)
        ? $ticketOverrides['quota_quantity']
        : 20;
    unset($ticketOverrides['quota_quantity']);

    TicketTypeQuota::create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => $ticketTypeQuantity,
    ]);

    $ticket = Ticket::create(array_merge([
        'ticket_type_id' => $ticketType->id,
        'price' => '49.90',
        'max_per_user' => 4,
    ], $ticketOverrides));

    return compact('event', 'ticketType', 'ticket');
}

describe('Checkout', function (): void {
    test('POST /checkout crea ordine, order items e completa hold', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createCheckoutTicketForTest();

        actingAs($user);

        post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ])->assertRedirect();

        $checkoutResponse = post('/checkout')->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
        expect($order)->not->toBeNull();
        $checkoutResponse->assertRedirect(route('orders.show', $order));
        expect($order->status)->toBe(OrderStatusEnum::COMPLETED);
        expect((float) $order->total_amount)->toBe(99.8);
        expect(OrderItem::query()->where('order_id', $order->id)->count())->toBe(1);

        $orderItem = OrderItem::query()->where('order_id', $order->id)->firstOrFail();
        expect((float) $orderItem->unit_price)->toBe(49.9);
        expect((int) $orderItem->quantity)->toBe(2);
        expect((int) $orderItem->ticket_id)->toBe($ticket->id);

        $hold = Hold::query()->where('user_id', $user->id)->firstOrFail();
        expect($hold->status)->toBe(HoldStatusEnum::COMPLETED);
    });

    test('POST /checkout senza autenticazione reindirizza al login', function (): void {
        post('/checkout')->assertRedirect('/login');
    });

    test('POST /checkout non crea ordine con hold scadute', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createCheckoutTicketForTest();

        actingAs($user);
        $hold = Hold::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'expires_at' => now()->subMinute(),
            'status' => HoldStatusEnum::ACTIVE,
        ]);

        $response = post('/checkout', ['hold_ids' => [$hold->id]]);
        $response->assertRedirect()->assertSessionHasErrors('holds');

        expect(Order::query()->count())->toBe(0);
    });

    test('POST /checkout blocca hold di altri utenti', function (): void {
        $owner = User::factory()->create();
        ['ticket' => $ticket] = createCheckoutTicketForTest();
        $otherUser = User::factory()->create();

        $hold = Hold::create([
            'user_id' => $owner->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'expires_at' => now()->addMinutes(10),
            'status' => HoldStatusEnum::ACTIVE,
        ]);

        actingAs($otherUser);

        $otherResponse = post('/checkout', ['hold_ids' => [$hold->id]])->assertRedirect()
            ->assertSessionHasErrors('holds');
        expect(Order::query()->count())->toBe(0);
    });

    test('POST /checkout con hold_ids usa solo quelli specificati', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticketA] = createCheckoutTicketForTest([
            'title' => 'Evento Checkout Test A',
        ]);
        ['ticket' => $ticketB] = createCheckoutTicketForTest([
            'title' => 'Evento Checkout Test B',
            'starts_at' => now()->addDays(12),
            'ends_at' => now()->addDays(12)->addHours(2),
        ]);

        actingAs($user);

        post('/cart/hold', ['ticket_id' => $ticketA->id, 'quantity' => 1])->assertRedirect();
        post('/cart/hold', ['ticket_id' => $ticketB->id, 'quantity' => 1])->assertRedirect();

        $holdA = Hold::query()->where('user_id', $user->id)->orderBy('id')->firstOrFail();
        $holdB = Hold::query()->where('user_id', $user->id)->orderBy('id')->skip(1)->first();

        post('/checkout', ['hold_ids' => [$holdA->id]])->assertRedirect();

        $order = Order::query()->where('user_id', $user->id)->latest('id')->firstOrFail();
        $items = OrderItem::query()->where('order_id', $order->id)->get();

        expect($items)->toHaveCount(1);
        expect($items->first()->ticket_id)->toBe($ticketA->id);
        $remainingHold = Hold::query()->find($holdB->id);
        expect($remainingHold->status)->toBe(HoldStatusEnum::ACTIVE);
    });

    test('GET /orders/{id} è accessibile solo al proprietario', function (): void {
        ['ticket' => $ticket] = createCheckoutTicketForTest();
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $order = Order::create([
            'user_id' => $owner->id,
            'status' => OrderStatusEnum::COMPLETED,
            'total_amount' => '49.90',
        ]);

        $order->orderItems()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'unit_price' => $ticket->price,
        ]);
        $orderUrl = route('orders.show', $order);

        get($orderUrl)->assertRedirect('/login');

        actingAs($intruder);
        get($orderUrl)->assertNotFound();

        actingAs($owner);
        get($orderUrl)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('app/orders/Show')
                ->has('order')
                ->where('order.id', $order->id)
            );
    });

    test('GET /orders/{id numerico} non è utilizzabile per enumerazione', function (): void {
        ['ticket' => $ticket] = createCheckoutTicketForTest();
        $owner = User::factory()->create();
        $order = Order::create([
            'user_id' => $owner->id,
            'status' => OrderStatusEnum::COMPLETED,
            'total_amount' => $ticket->price,
        ]);
        $order->orderItems()->create([
            'ticket_id' => $ticket->id,
            'quantity' => 1,
            'unit_price' => $ticket->price,
        ]);

        actingAs($owner);
        get('/orders/' . $order->id)->assertNotFound();
    });

    test('POST /checkout con idempotenza non crea doppio ordine', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createCheckoutTicketForTest();

        actingAs($user);
        post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ])->assertRedirect();

        post('/checkout')->assertRedirect();
        expect(Order::query()->count())->toBe(1);

        post('/checkout', [], ['referer' => '/checkout'])->assertRedirect('/checkout')->assertSessionHasErrors('holds');
        expect(Order::query()->count())->toBe(1);
    });

    test('POST /checkout senza hold nel carrello restituisce errore', function (): void {
        $user = User::factory()->create();

        actingAs($user);
        post('/checkout', [], ['referer' => '/checkout'])
            ->assertRedirect('/checkout')
            ->assertSessionHasErrors('holds');

        expect(Order::query()->count())->toBe(0);
    });

    test('GET /orders mostra solo gli ordini dell\'utente', function (): void {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        ['ticket' => $ownerTicket] = createCheckoutTicketForTest(['title' => 'Evento Owner']);
        ['ticket' => $otherTicket] = createCheckoutTicketForTest(['title' => 'Evento Altro']);

        $ownerOrder = Order::create([
            'user_id' => $owner->id,
            'status' => OrderStatusEnum::COMPLETED,
            'total_amount' => '39.00',
        ]);
        $ownerOrder->orderItems()->create([
            'ticket_id' => $ownerTicket->id,
            'quantity' => 1,
            'unit_price' => $ownerTicket->price,
        ]);

        $otherOrder = Order::create([
            'user_id' => $other->id,
            'status' => OrderStatusEnum::COMPLETED,
            'total_amount' => '29.00',
        ]);
        $otherOrder->orderItems()->create([
            'ticket_id' => $otherTicket->id,
            'quantity' => 1,
            'unit_price' => $otherTicket->price,
        ]);

        actingAs($owner);
        get('/orders')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('app/orders/Index')
                ->has('orders')
                ->has('totalOrders')
                ->where('totalOrders', 1)
                ->where('orders.0.public_id', $ownerOrder->public_id)
                ->where('orders.0.total_amount', '39.00')
            );
    });

    test('POST /checkout calcola totale e quantità corretti su ordini multi-ticket', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticketA] = createCheckoutTicketForTest([
            'title' => 'Evento Multi A',
        ], ['price' => '39.00', 'quota_quantity' => 20]);
        ['ticket' => $ticketB] = createCheckoutTicketForTest([
            'title' => 'Evento Multi B',
        ], ['price' => '15.50', 'quota_quantity' => 20]);

        actingAs($user);
        post('/cart/hold', ['ticket_id' => $ticketA->id, 'quantity' => 2])->assertRedirect();
        post('/cart/hold', ['ticket_id' => $ticketB->id, 'quantity' => 1])->assertRedirect();

        post('/checkout')->assertRedirect();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->firstOrFail();

        expect((float) $order->total_amount)->toBe(93.5);

        $itemsByTicket = OrderItem::query()
            ->where('order_id', $order->id)
            ->pluck('ticket_id')
            ->toArray();

        expect($itemsByTicket)->toContain($ticketA->id, $ticketB->id);
        expect((int) $order->orderItems()->sum('quantity'))->toBe(3);
    });

    test('GET /cart dopo checkout non contiene hold attivi', function (): void {
        $user = User::factory()->create();
        ['ticket' => $ticket] = createCheckoutTicketForTest();

        actingAs($user);
        post('/cart/hold', [
            'ticket_id' => $ticket->id,
            'quantity' => 1,
        ])->assertRedirect();

        post('/checkout')->assertRedirect();

        $response = get('/cart');
        $response->assertInertia(fn (Assert $page) => $page
            ->component('app/cart/Index')
            ->has('cart.items', 0)
        );
    });
});
