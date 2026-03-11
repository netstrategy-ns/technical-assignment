<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Http\Controllers\Admin\EventCategoryController;
use Inertia\Testing\AssertableInertia as Assert;

class EventCategoryControllerTest extends AdminControllersTestCase
{
    public function test_index_route_requires_authentication(): void
    {
        $response = $this->get(route('admin.event-categories.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_route_requires_admin_role(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.event-categories.index'));
        $response->assertRedirect(route('home'));
    }

    public function test_index_route_renders_expected_inertia_payload(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->get(route('admin.event-categories.index'));
        $response->assertOk();
        $response->assertInertia(function (Assert $page) {
            $page->component('admin/event-categories/Index')
                ->has('resource')
                ->where('resource', 'event-categories')
                ->has('rows')
                ->has('rows.data')
                ->has('rows.per_page')
                ->has('sort')
                ->has('filters')
                ->has('columns');
        });
    }

    public function test_index_route_can_be_queried_directly(): void
    {
        $request = $this->makeInertiaRequest('/admin/event-categories');
        $response = (new EventCategoryController())->index($request);

        $payload = $this->extractInertiaPayload($response, $request);
        $this->assertInertiaPayloadIndexShape($payload, 'admin/event-categories/Index', 'event-categories');
        $this->assertSame(24, $payload['props']['rows']['per_page']);
    }
}
