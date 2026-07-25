<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementActionPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_tables_use_consistent_supported_actions(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        app(StockService::class)->stockIn($product->id, 5, $admin->id, 'Opening stock');

        $this->actingAs($admin)
            ->get('/products')
            ->assertOk()
            ->assertSee('table-action--view', false)
            ->assertSee('table-action--edit', false)
            ->assertSee('table-action--delete', false);

        $this->actingAs($admin)
            ->get('/suppliers')
            ->assertOk()
            ->assertSee('table-action--view', false)
            ->assertSee('table-action--edit', false)
            ->assertSee('table-action--delete', false);

        $this->actingAs($admin)
            ->get('/stock-movements')
            ->assertOk()
            ->assertSee('table-action--view', false)
            ->assertDontSee('table-action--edit', false)
            ->assertDontSee('table-action--delete', false);
    }
}
