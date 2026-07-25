<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return Product::query()->with('supplier')
            ->when($this->filters['from'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($this->filters['to'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->when($this->filters['supplier_id'] ?? null, fn ($query, $value) => $query->where('supplier_id', $value))
            ->when($this->filters['category'] ?? null, fn ($query, $value) => $query->where('category', $value));
    }

    public function headings(): array
    {
        return ['ID', 'SKU', 'Name', 'Quantity', 'Minimum quantity', 'Price', 'Supplier', 'Category', 'Created at'];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $this->safeText($product->sku),
            $this->safeText($product->name),
            $product->quantity,
            $product->minimum_quantity,
            $product->price,
            $this->safeText($product->supplier?->name),
            $this->safeText($product->category),
            $product->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function safeText(?string $value): ?string
    {
        if ($value !== null && preg_match('/^[\s]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
