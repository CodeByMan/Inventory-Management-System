<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use LogicException;
use Tests\TestCase;

class MovementAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_movement_cannot_be_deleted_and_authorized_reversal_creates_linked_record(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $original = app(StockService::class)->stockIn($product->id, 10, $admin->id, 'Delivery');

        try {
            $original->delete();
            $this->fail('Historical movement deletion should be blocked.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('cannot be deleted', $exception->getMessage());
        }

        $response = $this->actingAs($admin)->post("/stock-movements/{$original->id}/reverse", ['reason' => 'Supplier delivery was entered twice']);
        $response->assertRedirect();

        $this->assertSame(0, $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'stock_out', 'quantity' => 10, 'reversal_of_id' => $original->id,
        ]);
        $this->assertDatabaseHas('stock_movements', ['id' => $original->id, 'type' => 'stock_in']);
    }

    public function test_reversing_withdrawal_for_archived_product_restores_product_and_stock(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $service = app(StockService::class);

        $service->stockIn(
            $product->id,
            5,
            $admin->id,
            'Opening stock',
        );

        $withdrawal = $service->stockOut(
            $product->id,
            5,
            $admin->id,
            'Stock withdrawn',
        );

        $product->archive();

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);

        $reversal = $service->reverse(
            $withdrawal->id,
            $admin->id,
            'Withdrawal entered incorrectly',
        );

        $this->assertNotSoftDeleted('products', [
            'id' => $product->id,
        ]);

        $this->assertSame(
            5,
            Product::findOrFail($product->id)->quantity,
        );

        $this->assertSame(
            $withdrawal->id,
            $reversal->reversal_of_id,
        );
    }

    public function test_web_reversal_for_archived_product_succeeds_instead_of_returning_404(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $service = app(StockService::class);

        $service->stockIn($product->id, 5, $admin->id, 'Opening stock');
        $withdrawal = $service->stockOut($product->id, 5, $admin->id, 'Stock withdrawn');
        $product->archive();

        $response = $this->actingAs($admin)->post(
            route('stock-movements.reverse', $withdrawal),
            ['reason' => 'Withdrawal entered incorrectly']
        );

        $reversal = $withdrawal->fresh()->reversal;

        $this->assertNotNull($reversal);
        $response->assertRedirect(route('stock-movements.show', $reversal));
        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
        $this->assertSame(5, Product::findOrFail($product->id)->quantity);
        $this->assertSame($withdrawal->id, $reversal->reversal_of_id);
    }

    public function test_reversed_withdrawal_is_excluded_from_top_products_report(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $service = app(StockService::class);
        $service->stockIn($product->id, 5, $admin->id, 'Opening stock');
        $withdrawal = $service->stockOut($product->id, 2, $admin->id, 'Temporary withdrawal');
        $service->reverse($withdrawal->id, $admin->id, 'Withdrawal was recorded in error');

        Sanctum::actingAs($admin);
        $this->getJson('/api/reports/top-products')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_same_movement_cannot_be_reversed_twice(): void
    {
        $admin = $this->userWithRole('admin');
        $product = Product::factory()->create();
        $original = app(StockService::class)->stockIn($product->id, 3, $admin->id);
        $service = app(StockService::class);
        $service->reverse($original->id, $admin->id, 'First correction');

        $this->expectException(ValidationException::class);
        $service->reverse($original->id, $admin->id, 'Duplicate correction');
    }
}
