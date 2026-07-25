<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_displays_public_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('StockManager')
            ->assertSee('Know what is in stock.')
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_api_product_resource_has_documented_english_fields(): void
    {
        $staff = $this->userWithRole('staff');
        Sanctum::actingAs($staff);
        Product::factory()->create(['sku' => 'ENG-001', 'name' => 'English Product']);
        $this->getJson('/api/products')->assertOk()->assertJsonStructure([
            'data' => [['id', 'sku', 'name', 'description', 'quantity', 'price', 'minimum_quantity', 'category', 'created_at', 'updated_at', 'archived_at']],
            'links', 'meta',
        ])->assertJsonMissingPath('data.0.nom')->assertJsonMissingPath('data.0.quantite');
    }

    public function test_patch_updates_only_supplied_product_fields(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);
        $product = Product::factory()->create([
            'name' => 'Original name',
            'price' => 25,
            'minimum_quantity' => 3,
        ]);

        $this->patchJson("/api/products/{$product->id}", ['name' => 'Patched name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Patched name');

        $fresh = $product->fresh();
        $this->assertSame('Patched name', $fresh->name);
        $this->assertSame('25.00', $fresh->price);
        $this->assertSame(3, $fresh->minimum_quantity);
    }

    public function test_no_duplicate_http_method_and_uri_pairs_exist(): void
    {
        $pairs = collect(Route::getRoutes())->flatMap(function ($route) {
            return collect($route->methods())->reject(fn ($method) => $method === 'HEAD')
                ->map(fn ($method) => $method.' '.$route->uri());
        });
        $this->assertSame($pairs->count(), $pairs->unique()->count(), 'Duplicate method/URI route detected.');
    }

    public function test_top_products_limit_is_validated(): void
    {
        $staff = $this->userWithRole('staff');
        Sanctum::actingAs($staff);

        $this->getJson('/api/reports/top-products?limit=not-a-number')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('limit');
    }

    public function test_legacy_french_api_paths_are_not_registered(): void
    {
        $this->getJson('/api/produits')->assertNotFound();
        $this->postJson('/api/stock/sortie')->assertNotFound();
    }
}
