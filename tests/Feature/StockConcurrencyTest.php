<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_withdrawal_reloads_and_rechecks_stock_inside_the_transaction(): void
    {
        $user = $this->userWithRole('staff');
        $product = Product::factory()->create();
        $service = app(StockService::class);
        $service->stockIn($product->id, 5, $user->id, 'Opening stock');

        // Two callers can both have observed 5. The service must not trust either stale observation.
        $service->stockOut($product->id, 4, $user->id, 'First request');

        try {
            $service->stockOut($product->id, 4, $user->id, 'Competing request');
            $this->fail('The second withdrawal should have failed after the locked recheck.');
        } catch (ValidationException $exception) {
            $this->assertSame('Insufficient stock for this withdrawal.', $exception->errors()['quantity'][0]);
        }

        $this->assertSame(1, $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('stock_movements', ['type' => 'stock_out', 'quantity_before' => 5, 'quantity_after' => 1]);
    }

    public function test_insufficient_stock_rolls_back_product_and_movement(): void
    {
        $user = $this->userWithRole('staff');
        $product = Product::factory()->create();

        try {
            app(StockService::class)->stockOut($product->id, 1, $user->id);
            $this->fail('Withdrawal should fail.');
        } catch (ValidationException) {
            $this->assertSame(0, $product->fresh()->quantity);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }
}
