<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebProductController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);

        $products = Product::with('supplier')
            ->when($request->string('search')->toString(), function ($query, $search): void {
                $query->where(fn ($innerQuery) => $innerQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%"));
            })
            ->when(
                $request->string('category')->toString(),
                fn ($query, $category) => $query->where('category', $category)
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $categories = Product::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        return view('products.create', ['suppliers' => Supplier::orderBy('name')->get()]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
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

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $movements = $product->stockMovements()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('products.show', [
            'product' => $product->load('supplier'),
            'movements' => $movements,
        ]);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->archive();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product archived successfully.');
    }
}
