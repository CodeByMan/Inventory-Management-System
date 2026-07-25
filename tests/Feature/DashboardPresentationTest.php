<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_has_one_named_user_dropdown_with_sign_out_only(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('class="user-menu"', false)
            ->assertSee($admin->name)
            ->assertSee('Sign Out')
            ->assertDontSee('Profile')
            ->assertSee('StockManager Developed by Muhammad Ali Nawaz');
    }
}
