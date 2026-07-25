<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function dashboard(): View
    {
        $start = now()->startOfDay()->subDays(29);
        $dates = collect(range(0, 29))->map(fn (int $day) => $start->copy()->addDays($day)->format('Y-m-d'));
        $totals = StockMovement::query()
            ->select(DB::raw('DATE(created_at) as movement_date'), 'type', DB::raw('SUM(quantity) as total'))
            ->where('created_at', '>=', $start)->groupBy('movement_date', 'type')->get();

        $stockIn = $dates->map(fn ($date) => (int) optional($totals->first(fn ($r) => $r->movement_date === $date && $r->type === StockMovement::TYPE_IN))->total);
        $stockOut = $dates->map(fn ($date) => (int) optional($totals->first(fn ($r) => $r->movement_date === $date && $r->type === StockMovement::TYPE_OUT))->total);

        return view('dashboard', [
            'productCount' => Product::count(),
            'lowStockCount' => Product::whereColumn('quantity', '<=', 'minimum_quantity')->count(),
            'movementCount' => StockMovement::count(),
            'stockInTotal' => StockMovement::where('type', StockMovement::TYPE_IN)->sum('quantity'),
            'stockOutTotal' => StockMovement::where('type', StockMovement::TYPE_OUT)->sum('quantity'),
            'recentMovements' => StockMovement::with(['product', 'user'])->latest()->limit(8)->get(),
            'dates' => $dates,
            'stockIn' => $stockIn,
            'stockOut' => $stockOut,
        ]);
    }
}
