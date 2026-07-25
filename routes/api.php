<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('api.login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => response()->json([
        'data' => ['id' => $request->user()->id, 'name' => $request->user()->name, 'email' => $request->user()->email, 'roles' => $request->user()->getRoleNames()],
    ]))->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::middleware('role:admin|staff')->group(function (): void {
        Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
        Route::get('/products/export', [ProductController::class, 'export'])->name('api.products.export');
        Route::get('/products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('api.products.show');
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('api.suppliers.index');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->whereNumber('supplier')->name('api.suppliers.show');
        Route::post('/stock/in', [StockController::class, 'stockIn'])->name('api.stock.in');
        Route::post('/stock/out', [StockController::class, 'stockOut'])->name('api.stock.out');
        Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('api.reports.low-stock');
        Route::get('/reports/top-products', [ReportController::class, 'topProducts'])->name('api.reports.top-products');
        Route::get('/reports/history', [ReportController::class, 'history'])->name('api.reports.history');
        Route::get('/reports/stock/pdf', [ReportController::class, 'stockPdf'])->name('api.reports.stock-pdf');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
        Route::match(['put', 'patch'], '/products/{product}', [ProductController::class, 'update'])->whereNumber('product')->name('api.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->whereNumber('product')->name('api.products.destroy');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('api.suppliers.store');
        Route::match(['put', 'patch'], '/suppliers/{supplier}', [SupplierController::class, 'update'])->whereNumber('supplier')->name('api.suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->whereNumber('supplier')->name('api.suppliers.destroy');
        Route::post('/stock-movements/{stockMovement}/reverse', [StockController::class, 'reverse'])->whereNumber('stockMovement')->name('api.stock-movements.reverse');
    });
});
