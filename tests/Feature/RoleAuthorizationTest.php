<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_without_role_is_denied(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->getJson('/api/products')->assertForbidden();
    }

    public function test_staff_can_view_and_move_stock_but_cannot_manage_product_metadata(): void
    {
        $staff = $this->userWithRole('staff');
        Sanctum::actingAs($staff);
        $product = Product::factory()->create();

        $this->getJson('/api/products')->assertOk();
        $this->postJson('/api/stock/in', ['product_id' => $product->id, 'quantity' => 5])->assertCreated();
        $this->postJson('/api/products', ['name' => 'Denied', 'price' => 1, 'minimum_quantity' => 0])->assertForbidden();
    }

    public function test_admin_can_manage_products_and_reverse_movements(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);
        $created = $this->postJson('/api/products', ['name' => 'Allowed', 'price' => 1, 'minimum_quantity' => 0, 'opening_stock' => 2])->assertCreated();
        $productId = $created->json('data.id');
        $movementId = Product::findOrFail($productId)->stockMovements()->firstOrFail()->id;
        $this->postJson("/api/stock-movements/{$movementId}/reverse", ['reason' => 'Correcting duplicate opening stock'])->assertCreated();
    }
}
