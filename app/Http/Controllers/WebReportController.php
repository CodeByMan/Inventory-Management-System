<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class WebReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function lowStock(): View
    {
        $products = Product::with('supplier')
            ->whereColumn('quantity', '<=', 'minimum_quantity')
            ->paginate(20);

        return view('reports.low_stock', compact('products'));
    }

    public function topProducts(): View
    {
        $products = StockMovement::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->where('type', StockMovement::TYPE_OUT)
            ->whereNull('reversal_of_id')
            ->whereDoesntHave('reversal')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product')
            ->limit(20)
            ->get();

        return view('reports.top_products', compact('products'));
    }

    public function history(Request $request): View
    {
        $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'type' => ['nullable', 'in:stock_in,stock_out'],
        ]);

        $movements = StockMovement::with(['product', 'user'])
            ->when($request->integer('product_id'), fn ($query, $value) => $query->where('product_id', $value))
            ->when($request->string('type')->toString(), fn ($query, $value) => $query->where('type', $value))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('reports.history', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function stockPdf(Request $request): Response
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $products = Product::with('supplier')
            ->when($request->date('from'), fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($request->date('to'), fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->get();

        return Pdf::loadView('reports.stock_pdf', [
            'products' => $products,
            'generatedAt' => now(),
        ])->download('stock-report.pdf');
    }
}
