@extends('layouts.app')
@section('title','Edit Product')

@section('content')

<div class="section-header">
    <h1>Edit Product</h1>
</div>

<div class="card">
    <div class="card-header">
        <h4>Form Edit Product</h4>
    </div>

    <div class="card-body">
        <form action="{{ route('product.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>SKU</label>
                <input type="text"
                       name="sku"
                       class="form-control @error('sku') is-invalid @enderror"
                       value="{{ old('sku', $product->sku) }}"
                       required>
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Nama Product</label>
                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $product->name) }}"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Stock</label>
                <input type="number"
                       name="stock"
                       class="form-control @error('stock') is-invalid @enderror"
                       value="{{ old('stock', $product->stock) }}"
                       min="0"
                       required>
                @error('stock')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Unit</label>
                <input type="text"
                       name="unit"
                       class="form-control @error('unit') is-invalid @enderror"
                       value="{{ old('unit', $product->unit) }}"
                       required>
                @error('unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-right">
                <a href="{{ route('product.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
                <button class="btn btn-warning">
                    Update
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
