<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The whole application: a stock list with search, a low stock filter and the
 * usual create / edit / delete.
 */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $lowOnly = $request->boolean('low');

        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('sku', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%');
                });
            })
            ->when($lowOnly, fn ($query) => $query->lowStock())
            ->orderBy('name')
            ->get();

        return view('products.index', [
            'products' => $products,
            'search' => $search,
            'lowOnly' => $lowOnly,
            'totalProducts' => Product::count(),
            'lowStockCount' => Product::lowStock()->count(),
            'stockValue' => $products->sum(fn (Product $product) => $product->stockValue()),
        ]);
    }

    public function create(): View
    {
        return view('products.form', [
            'product' => new Product(['quantity' => 0, 'reorder_level' => 0, 'unit_price' => 0]),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()->route('products.index')->with('status', 'Product added.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', ['product' => $product]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product deleted.');
    }
}
