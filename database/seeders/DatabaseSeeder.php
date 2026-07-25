<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'Demo seeding is allowed only in local and testing environments.'
            );
        }

        if (! config('stockmanager.allow_demo_seeding')) {
            throw new RuntimeException(
                'Set ALLOW_DEMO_SEEDING=true to create demo records.'
            );
        }

        $password = config('stockmanager.demo_password');

        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException(
                'DEMO_PASSWORD must contain at least 12 characters.'
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $adminRole = Role::findOrCreate('admin', 'web');
        Role::findOrCreate('staff', 'web');

        $admin = User::updateOrCreate(
            ['email' => config('stockmanager.demo_email')],
            [
                'name' => config('stockmanager.demo_name'),
                'password' => Hash::make($password),
            ]
        );
        $admin->forceFill(['email_verified_at' => now()])->save();
        $admin->syncRoles([$adminRole]);

        $suppliers = collect([
            [
                'name' => 'Karachi Office Supplies',
                'email' => 'orders@karachioffice.test',
                'phone' => '+92 21 555 0101',
                'address' => 'Karachi, Pakistan',
            ],
            [
                'name' => 'Tech Distribution Hub',
                'email' => 'sales@techdistribution.test',
                'phone' => '+92 21 555 0102',
                'address' => 'Karachi, Pakistan',
            ],
            [
                'name' => 'National Hardware Traders',
                'email' => 'contact@nationalhardware.test',
                'phone' => '+92 21 555 0103',
                'address' => 'Lahore, Pakistan',
            ],
        ])->map(fn (array $data) => Supplier::updateOrCreate(['email' => $data['email']], $data));

        $catalogue = [
            [
                'sku' => 'OFF-001',
                'name' => 'A4 Copy Paper Box',
                'description' => 'Five reams of standard A4 office paper.',
                'price' => 3850,
                'minimum_quantity' => 5,
                'supplier_id' => $suppliers[0]->id,
                'category' => 'Office',
                'opening_stock' => 24,
            ],
            [
                'sku' => 'OFF-002',
                'name' => 'Ballpoint Pen Pack',
                'description' => 'Pack of ten blue ballpoint pens.',
                'price' => 650,
                'minimum_quantity' => 10,
                'supplier_id' => $suppliers[0]->id,
                'category' => 'Office',
                'opening_stock' => 55,
            ],
            [
                'sku' => 'ELE-001',
                'name' => 'Wireless Keyboard',
                'description' => 'Compact wireless keyboard for office workstations.',
                'price' => 4200,
                'minimum_quantity' => 4,
                'supplier_id' => $suppliers[1]->id,
                'category' => 'Electronics',
                'opening_stock' => 18,
            ],
            [
                'sku' => 'ELE-002',
                'name' => 'USB-C Dock',
                'description' => 'Multi-port USB-C docking station.',
                'price' => 9800,
                'minimum_quantity' => 3,
                'supplier_id' => $suppliers[1]->id,
                'category' => 'Electronics',
                'opening_stock' => 11,
            ],
            [
                'sku' => 'HRD-001',
                'name' => 'Tool Set',
                'description' => 'General-purpose 42-piece hand tool set.',
                'price' => 7200,
                'minimum_quantity' => 2,
                'supplier_id' => $suppliers[2]->id,
                'category' => 'Hardware',
                'opening_stock' => 8,
            ],
        ];

        $stock = app(StockService::class);

        foreach ($catalogue as $item) {
            $openingStock = $item['opening_stock'];
            unset($item['opening_stock']);

            $product = Product::firstOrCreate(['sku' => $item['sku']], $item);

            if ($product->stockMovements()->doesntExist() && $openingStock > 0) {
                $stock->stockIn(
                    $product->id,
                    $openingStock,
                    $admin->id,
                    'Opening stock from demo seeder'
                );
            }
        }
    }
}
