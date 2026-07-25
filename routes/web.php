<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WebProductController;
use App\Http\Controllers\WebReportController;
use App\Http\Controllers\WebStockMovementController;
use App\Http\Controllers\WebSupplierController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
});

Route::post('/logout', [WebAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin|staff'])->group(function (): void {
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/products', [WebProductController::class, 'index'])->name('products.index');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('/products/{product}', [WebProductController::class, 'show'])->whereNumber('product')->name('products.show');
    Route::get('/suppliers', [WebSupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}', [WebSupplierController::class, 'show'])->whereNumber('supplier')->name('suppliers.show');
    Route::get('/stock-movements', [WebStockMovementController::class, 'index'])->name('stock-movements.index');
    Route::get('/stock-movements/create', [WebStockMovementController::class, 'create'])->name('stock-movements.create');
    Route::post('/stock-movements', [WebStockMovementController::class, 'store'])->name('stock-movements.store');
    Route::get('/stock-movements/{stockMovement}', [WebStockMovementController::class, 'show'])->whereNumber('stockMovement')->name('stock-movements.show');
    Route::get('/reports', [WebReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/low-stock', [WebReportController::class, 'lowStock'])->name('reports.low-stock');
    Route::get('/reports/top-products', [WebReportController::class, 'topProducts'])->name('reports.top-products');
    Route::get('/reports/history', [WebReportController::class, 'history'])->name('reports.history');
    Route::get('/reports/stock/pdf', [WebReportController::class, 'stockPdf'])->name('reports.stock-pdf');
});

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/products/create', [WebProductController::class, 'create'])->name('products.create');
    Route::post('/products', [WebProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [WebProductController::class, 'edit'])->whereNumber('product')->name('products.edit');
    Route::match(['put', 'patch'], '/products/{product}', [WebProductController::class, 'update'])->whereNumber('product')->name('products.update');
    Route::delete('/products/{product}', [WebProductController::class, 'destroy'])->whereNumber('product')->name('products.destroy');
    Route::get('/suppliers/create', [WebSupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [WebSupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', [WebSupplierController::class, 'edit'])->whereNumber('supplier')->name('suppliers.edit');
    Route::match(['put', 'patch'], '/suppliers/{supplier}', [WebSupplierController::class, 'update'])->whereNumber('supplier')->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [WebSupplierController::class, 'destroy'])->whereNumber('supplier')->name('suppliers.destroy');
    Route::post('/stock-movements/{stockMovement}/reverse', [WebStockMovementController::class, 'reverse'])->whereNumber('stockMovement')->name('stock-movements.reverse');
});
