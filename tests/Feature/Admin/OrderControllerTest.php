<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\OrderController;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

class OrderControllerTest extends AdminControllersTestCase
{
    public function test_index_route_requires_authentication(): void
    {
        $response = $this->get(route('admin.orders.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_route_requires_admin_role(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.orders.index'));
        $response->assertRedirect(route('home'));
    }

    public function test_index_route_renders_expected_inertia_payload(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->get(route('admin.orders.index'));
        $response->assertOk();
        $response->assertInertia(function (Assert $page) {
            $page->component('admin/orders/Index')
                ->has('resource')
                ->where('resource', 'orders')
                ->has('rows')
                ->has('rows.data')
                ->has('rows.per_page')
                ->has('sort')
                ->has('filters')
                ->has('columns');
        });
    }

    public function test_index_route_can_be_called_directly(): void
    {
        $request = $this->makeInertiaRequest('/admin/orders');
        $response = (new OrderController())->index($request);

        $payload = $this->extractInertiaPayload($response, $request);
        $this->assertInertiaPayloadIndexShape($payload, 'admin/orders/Index', 'orders');
        $this->assertSame(24, $payload['props']['rows']['per_page']);
    }
}
