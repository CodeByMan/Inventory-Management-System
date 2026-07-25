<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function lowStock(): AnonymousResourceCollection
    {
        $products = Product::with('supplier')
            ->whereColumn('quantity', '<=', 'minimum_quantity')
            ->paginate(15);

        return ProductResource::collection($products);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'between:1,50'],
        ]);
        $limit = (int) ($validated['limit'] ?? 10);

        $rows = StockMovement::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('type', StockMovement::TYPE_OUT)
            ->whereNull('reversal_of_id')
            ->whereDoesntHave('reversal')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product:id,sku,name')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $rows->map(fn ($row) => [
                'product' => $row->product ? [
                    'id' => $row->product->id,
                    'sku' => $row->product->sku,
                    'name' => $row->product->name,
                ] : null,
                'total_quantity' => (int) $row->total_quantity,
            ]),
        ]);
    }

    public function history(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'type' => ['nullable', 'in:stock_in,stock_out'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $movements = StockMovement::with(['product.supplier', 'user'])
            ->when($validated['product_id'] ?? null, fn ($query, $value) => $query->where('product_id', $value))
            ->when($validated['type'] ?? null, fn ($query, $value) => $query->where('type', $value))
            ->when($validated['from'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($validated['to'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->latest()
            ->paginate(25);

        return StockMovementResource::collection($movements);
    }

    public function stockPdf(Request $request): Response
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $products = Product::with('supplier')
            ->when($filters['from'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['to'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->get();

        $pdf = Pdf::loadView('reports.stock_pdf', [
            'products' => $products,
            'generatedAt' => now(),
        ]);

        return $pdf->download('stock-report.pdf');
    }
}
