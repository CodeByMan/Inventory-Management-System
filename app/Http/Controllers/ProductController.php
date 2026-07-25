<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:120'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $products = Product::query()
            ->with('supplier')
            ->when($validated['category'] ?? null, fn ($query, $value) => $query->where('category', $value))
            ->when($validated['supplier_id'] ?? null, fn ($query, $value) => $query->where('supplier_id', $value))
            ->when($validated['search'] ?? null, function ($query, $value): void {
                $query->where(fn ($innerQuery) => $innerQuery
                    ->where('name', 'like', "%{$value}%")
                    ->orWhere('sku', 'like', "%{$value}%"));
            })
            ->latest('id')
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $openingStock = (int) ($data['opening_stock'] ?? 0);
        unset($data['opening_stock']);

        $product = DB::transaction(function () use ($data, $openingStock, $request): Product {
            $product = Product::create($data);

            if ($openingStock > 0) {
                $this->stockService->stockIn(
                    $product->id,
                    $openingStock,
                    $request->user()->id,
                    'Opening stock'
                );
            }

            return $product;
        });

        return (new ProductResource($product->fresh('supplier')))->response()->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('supplier'));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product->fresh('supplier'));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->archive();

        return response()->json(['message' => 'Product archived successfully.']);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);

        return Excel::download(new ProductsExport($filters), 'products.xlsx');
    }
}
