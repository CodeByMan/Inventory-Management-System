<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebStockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_flow_uses_atomic_service_and_never_allows_negative_stock(): void
    {
        $staff = $this->userWithRole('staff');
        $product = Product::factory()->create();
        app(StockService::class)->stockIn($product->id, 3, $staff->id, 'Opening');

        $this->actingAs($staff)->post('/stock-movements', [
            'product_id' => $product->id, 'type' => 'stock_out', 'quantity' => 2, 'note' => 'Order',
        ])->assertRedirect();

        $this->actingAs($staff)->from('/stock-movements/create')->post('/stock-movements', [
            'product_id' => $product->id, 'type' => 'stock_out', 'quantity' => 2, 'note' => 'Second order',
        ])->assertRedirect('/stock-movements/create')->assertSessionHasErrors('quantity');

        $this->assertSame(1, $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 2);
    }
}
