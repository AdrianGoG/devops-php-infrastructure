@extends('layouts.app')

@section('title', 'Stock')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">Stock</h1>
            <p class="page-subtitle mb-0">
                {{ $products->count() }} {{ Str::plural('product', $products->count()) }} shown
                @if ($search !== '' || $lowOnly)
                    · <a href="{{ route('products.index') }}">clear filters</a>
                @endif
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <span class="pill">Catalogue: {{ $totalProducts }}</span>
            <span class="pill {{ $lowStockCount > 0 ? 'pill-danger' : 'pill-ok' }}">
                Low stock: {{ $lowStockCount }}
            </span>
            <span class="pill pill-ok">Value: {{ number_format($stockValue, 2) }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="card-surface card-pad mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label for="q" class="form-label">Search</label>
                <input type="text" id="q" name="q" value="{{ $search }}"
                       class="form-control" placeholder="SKU, name or location">
            </div>

            <div class="col-md-3">
                <div class="form-check mt-md-4">
                    <input class="form-check-input" type="checkbox" id="low" name="low" value="1"
                           {{ $lowOnly ? 'checked' : '' }}>
                    <label class="form-check-label" for="low">Only low stock</label>
                </div>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-ghost w-100">Filter</button>
            </div>
        </div>
    </form>

    <div class="card-surface">
        <div class="table-responsive">
            <table class="table table-crm align-middle">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th class="text-end">In stock</th>
                        <th class="text-end">Reorder at</th>
                        <th class="text-end">Unit price</th>
                        <th class="text-end">Stock value</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="mono cell-strong">{{ $product->sku }}</td>
                            <td>
                                <span class="cell-strong d-block">{{ $product->name }}</span>
                                <span class="text-dim small">{{ $product->location ?? 'no location' }}</span>
                            </td>
                            <td class="text-end">
                                @if ($product->isLowStock())
                                    <span class="pill pill-danger">{{ $product->quantity }}</span>
                                @else
                                    <span class="cell-strong">{{ $product->quantity }}</span>
                                @endif
                            </td>
                            <td class="text-end text-dim">{{ $product->reorder_level }}</td>
                            <td class="text-end">{{ number_format((float) $product->unit_price, 2) }}</td>
                            <td class="text-end cell-strong">{{ number_format($product->stockValue(), 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-ghost btn-sm">Edit</a>

                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete {{ $product->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-soft btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-dim py-4">
                                No product matches the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
