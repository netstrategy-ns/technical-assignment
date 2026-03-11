<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_new_users_can_register_to_requested_redirect_path()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'auth_redirect' => '/orders',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/orders');
    }

    public function test_new_users_register_with_invalid_auth_redirect_is_sanitized()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User 2',
            'email' => 'test3@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'auth_redirect' => 'https://evil.com/phish',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }
}
