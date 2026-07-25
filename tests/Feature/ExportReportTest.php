<?php

namespace Tests\Feature;

use App\Exports\ProductsExport;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExportReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_mapping_neutralizes_formula_like_text(): void
    {
        $product = Product::factory()->create([
            'sku' => '=1+1',
            'name' => '@malicious',
            'category' => '+formula',
        ])->load('supplier');

        $row = (new ProductsExport)->map($product);

        $this->assertSame("'=1+1", $row[1]);
        $this->assertSame("'@malicious", $row[2]);
        $this->assertSame("'+formula", $row[7]);
    }

    public function test_excel_and_pdf_exports_return_expected_content_types(): void
    {
        $staff = $this->userWithRole('staff');
        Sanctum::actingAs($staff);
        Product::factory()->create();

        $this->get('/api/products/export', ['Accept' => 'application/json'])
            ->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->get('/api/reports/stock/pdf', ['Accept' => 'application/json'])
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
