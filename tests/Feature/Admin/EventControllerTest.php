<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\EventController;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

class EventControllerTest extends AdminControllersTestCase
{
    public function test_index_route_requires_authentication(): void
    {
        $response = $this->get(route('admin.events.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_route_requires_admin_role(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.events.index'));
        $response->assertRedirect(route('home'));
    }

    public function test_index_route_renders_expected_inertia_payload(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->get(route('admin.events.index'));
        $response->assertOk();
        $response->assertInertia(function (Assert $page) {
            $page->component('admin/events/Index')
                ->has('resource')
                ->where('resource', 'events')
                ->has('rows')
                ->has('rows.data')
                ->has('rows.per_page')
                ->has('sort')
                ->has('filters')
                ->has('columns');
        });
    }

    public function test_index_route_accepts_valid_and_invalid_per_page_values(): void
    {
        $user = $this->adminUser();

        $validResponse = $this->actingAs($user)->get(route('admin.events.index', ['per_page' => 12]));
        $validResponse->assertOk();
        $this->assertSame(12, $validResponse->inertiaProps('rows.per_page'));

        $invalidResponse = $this->actingAs($user)->get(route('admin.events.index', ['per_page' => 11]));
        $invalidResponse->assertOk();
        $this->assertSame(24, $invalidResponse->inertiaProps('rows.per_page'));

        $request = $this->makeInertiaRequest('/admin/events');
        $response = (new EventController())->index($request);
        $payload = $this->extractInertiaPayload($response, $request);
        $this->assertInertiaPayloadIndexShape($payload, 'admin/events/Index', 'events');
    }
}
