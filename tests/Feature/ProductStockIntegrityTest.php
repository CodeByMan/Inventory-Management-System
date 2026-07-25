<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductStockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_stock_creates_reconciling_ledger_movement(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/products', [
            'sku' => 'TEST-001', 'name' => 'Test Product', 'price' => 25,
            'minimum_quantity' => 2, 'opening_stock' => 12, 'category' => 'Testing',
        ])->assertCreated();

        $product = Product::findOrFail($response->json('data.id'));
        $this->assertSame(12, $product->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id, 'type' => 'stock_in', 'quantity' => 12,
            'quantity_before' => 0, 'quantity_after' => 12, 'note' => 'Opening stock',
        ]);
    }

    public function test_product_update_cannot_silently_change_quantity(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);
        $product = Product::factory()->create(['name' => 'Original']);

        $this->putJson("/api/products/{$product->id}", [
            'name' => 'Changed', 'price' => 10, 'minimum_quantity' => 1, 'quantity' => 999,
        ])->assertUnprocessable()->assertJsonValidationErrors('quantity');

        $this->assertSame(0, $product->fresh()->quantity);
        $this->assertSame('Original', $product->fresh()->name);
    }

    public function test_product_with_stock_cannot_be_archived(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);
        $product = Product::factory()->create();
        app(StockService::class)->stockIn($product->id, 2, $admin->id, 'Opening stock');

        $this->deleteJson("/api/products/{$product->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product');

        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
        $this->assertSame(2, $product->fresh()->quantity);
    }

    public function test_archived_zero_stock_product_remains_visible_from_historical_movement(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $movement = app(StockService::class)->stockIn($product->id, 1, $admin->id, 'Temporary stock');
        app(StockService::class)->stockOut($product->id, 1, $admin->id, 'Stock cleared');

        $product->archive();

        $this->assertSame($product->id, $movement->fresh()->product->id);
        $this->assertTrue($movement->fresh()->product->trashed());
    }

    public function test_product_delete_is_soft_delete(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);
        $product = Product::factory()->create();
        $this->deleteJson("/api/products/{$product->id}")->assertOk();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
