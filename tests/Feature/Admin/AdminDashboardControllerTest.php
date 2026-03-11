<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

class AdminDashboardControllerTest extends AdminControllersTestCase
{
    public function test_dashboard_route_is_not_accessible_to_guests(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_route_is_not_accessible_to_non_admin_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertRedirect(route('home'));
    }

    public function test_dashboard_route_renders_admin_dashboard_page_for_admin_users(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertInertia(function (Assert $page) {
            $page->component('admin/dashboard/Index');
        });
    }
}
