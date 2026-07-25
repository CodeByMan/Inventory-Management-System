<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_documented_account_roles_and_reconciled_stock(): void
    {
        config()->set([
            'stockmanager.allow_demo_seeding' => true,
            'stockmanager.demo_email' => 'admin@example.com',
            'stockmanager.demo_password' => 'StrongLocalPassword!2026',
        ]);
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('StrongLocalPassword!2026', $admin->password));
        $this->assertTrue($admin->hasRole('admin'));

        Product::all()->each(function (Product $product): void {
            $in = $product->stockMovements()->where('type', 'stock_in')->sum('quantity');
            $out = $product->stockMovements()->where('type', 'stock_out')->sum('quantity');
            $this->assertSame($in - $out, $product->quantity);
        });
    }
}
