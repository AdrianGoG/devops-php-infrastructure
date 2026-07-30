@extends('layouts.app')

@section('title', $product->exists ? 'Edit '.$product->name : 'New product')

@section('content')
    <div class="d-flex align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $product->exists ? 'Edit product' : 'New product' }}</h1>
            <p class="page-subtitle mb-0">A product is low on stock when the quantity reaches the reorder level.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Back to stock</a>
    </div>

    @if ($errors->any())
        <div class="alert-soft alert-soft-err mb-4">
            The form has {{ $errors->count() }} {{ Str::plural('error', $errors->count()) }}.
            Please check the fields below.
        </div>
    @endif

    <form method="POST"
          action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}"
          class="card-surface card-pad" novalidate>
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-4">
                <label for="sku" class="form-label">SKU *</label>
                <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}"
                       class="form-control mono @error('sku') is-invalid @enderror" placeholder="SRV-1120">
                @error('sku')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="name" class="form-label">Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="quantity" class="form-label">In stock *</label>
                <input type="number" id="quantity" name="quantity" min="0"
                       value="{{ old('quantity', $product->quantity) }}"
                       class="form-control @error('quantity') is-invalid @enderror">
                @error('quantity')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="reorder_level" class="form-label">Reorder level *</label>
                <input type="number" id="reorder_level" name="reorder_level" min="0"
                       value="{{ old('reorder_level', $product->reorder_level) }}"
                       class="form-control @error('reorder_level') is-invalid @enderror">
                @error('reorder_level')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="unit_price" class="form-label">Unit price *</label>
                <input type="number" id="unit_price" name="unit_price" step="0.01" min="0"
                       value="{{ old('unit_price', $product->unit_price) }}"
                       class="form-control @error('unit_price') is-invalid @enderror">
                @error('unit_price')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" id="location" name="location" value="{{ old('location', $product->location) }}"
                       class="form-control @error('location') is-invalid @enderror" placeholder="A-01">
                @error('location')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-accent">
                    {{ $product->exists ? 'Save changes' : 'Add product' }}
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
@endsection
