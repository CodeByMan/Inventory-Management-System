<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_landing_page_uses_existing_brand_and_login_navigation(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('StockManager')
            ->assertSee('Log in to StockManager')
            ->assertSee('Products')
            ->assertSee('Suppliers')
            ->assertSee('Stock movements')
            ->assertSee('Reports')
            ->assertSee('images/stockmanager-warehouse-hero.webp', false)
            ->assertDontSee('Start Free Trial')
            ->assertDontSee('Pricing')
            ->assertSee('StockManager Developed by Muhammad Ali Nawaz');
    }

    public function test_authenticated_landing_page_preserves_existing_application_navigation(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee('href="'.route('products.index').'"', false)
            ->assertSee('href="'.route('suppliers.index').'"', false)
            ->assertSee('href="'.route('stock-movements.index').'"', false)
            ->assertSee('href="'.route('reports.index').'"', false)
            ->assertSee('Log out');
    }
}
