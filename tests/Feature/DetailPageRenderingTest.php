<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailPageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_render_all_management_detail_pages(): void
    {
        $admin = $this->userWithRole('admin');
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->for($supplier)->create();

        $movement = app(StockService::class)->stockIn(
            $product->id,
            5,
            $admin->id,
            'Detail-page rendering test'
        );

        $this->actingAs($admin)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->name);

        $this->actingAs($admin)
            ->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee($supplier->name);

        $this->actingAs($admin)
            ->get(route('stock-movements.show', $movement))
            ->assertOk()
            ->assertSee('Stock movement #'.$movement->id);
    }
}
