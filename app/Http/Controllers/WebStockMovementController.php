<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebStockMovementController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function index(Request $request): View
    {
        $request->validate([
            'type' => ['nullable', 'in:stock_in,stock_out'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $movements = StockMovement::with(['product', 'user', 'reversal'])
            ->when($request->string('type')->toString(), fn ($query, $value) => $query->where('type', $value))
            ->when($request->integer('product_id'), fn ($query, $value) => $query->where('product_id', $value))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('stock_movements.index', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('stock_movements.create', ['products' => Product::orderBy('name')->get()]);
    }

    public function store(StockRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $type = $request->validate([
            'type' => ['required', 'in:stock_in,stock_out'],
        ])['type'];

        $movement = $type === StockMovement::TYPE_IN
            ? $this->stockService->stockIn(
                $data['product_id'],
                $data['quantity'],
                $request->user()->id,
                $data['note'] ?? null
            )
            : $this->stockService->stockOut(
                $data['product_id'],
                $data['quantity'],
                $request->user()->id,
                $data['note'] ?? null
            );

        return redirect()
            ->route('stock-movements.show', $movement)
            ->with('success', 'Stock movement recorded successfully.');
    }

    public function show(StockMovement $stockMovement): View
    {
        return view('stock_movements.show', [
            'movement' => $stockMovement->load(['product', 'user', 'reversalOf', 'reversal']),
        ]);
    }

    public function reverse(Request $request, StockMovement $stockMovement): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        $reversal = $this->stockService->reverse(
            $stockMovement->id,
            $request->user()->id,
            $data['reason']
        );

        return redirect()
            ->route('stock-movements.show', $reversal)
            ->with('success', 'Compensating reversal recorded successfully.');
    }
}
