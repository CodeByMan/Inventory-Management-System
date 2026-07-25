<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_api_request_returns_401(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
    }

    public function test_web_session_is_not_accepted_by_bearer_only_api(): void
    {
        $user = $this->userWithRole('admin');

        $this->actingAs($user)
            ->getJson('/api/products')
            ->assertUnauthorized();
    }

    public function test_bearer_token_login_and_authenticated_request_work(): void
    {
        $user = $this->userWithRole('admin');

        $user->update([
            'password' => 'CorrectHorseBatteryStaple!',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'CorrectHorseBatteryStaple!',
            'device_name' => 'phpunit',
        ])
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer');

        $this->withToken($login->json('token'))
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_invalid_api_credentials_return_401(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_web_login_form_contains_csrf_token_and_no_global_csrf_exclusion_is_configured(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="_token"', false);

        $bootstrap = file_get_contents(
            base_path('bootstrap/app.php')
        );

        $this->assertIsString($bootstrap);

        $this->assertStringNotContainsString(
            'validateCsrfTokens(except',
            $bootstrap
        );

        $this->assertStringNotContainsString(
            'statefulApi(',
            $bootstrap
        );
    }

    public function test_sanctum_stateful_guards_are_disabled(): void
    {
        $this->assertSame([], config('sanctum.stateful'));
        $this->assertSame([], config('sanctum.guard'));
    }
}
