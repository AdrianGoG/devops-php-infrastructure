<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| app-inventory · VM3 Application Server 2 · PHP 8.0 · Laravel 9 · port 8082
|
*/

Route::get('/', fn () => redirect()->route('products.index'));

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

/*
| Health endpoint with the same contract as the other applications of the
| estate, so the Python monitoring utility treats all nine the same way.
*/
Route::get('/health', function () {
    $database = 'ok';
    $products = null;

    try {
        $products = \App\Models\Product::count();
    } catch (\Throwable $exception) {
        $database = 'unavailable';
        report($exception);
    }

    return response()->json([
        'status' => $database === 'ok' ? 'ok' : 'degraded',
        'application' => 'app-inventory',
        'server' => 'VM3-Application-Server-2',
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'environment' => app()->environment(),
        'database' => $database,
        'products' => $products,
        'checked_at' => now()->toIso8601String(),
    ], $database === 'ok' ? 200 : 503);
})->name('health');
