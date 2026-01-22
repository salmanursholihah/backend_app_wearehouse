@extends('layouts.app')
@section('title','Product Gudang')

@section('content')
<div class="section-header d-flex justify-content-between">
    <h1>Product Gudang</h1>
</div>

<div class="card">
    <div class="card-header">
        <h4>Daftar Product</h4>
    </div>

    <div class="card-body table-responsive">
        <a href="{{ route('product.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Product
    </a>
    <br>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Product</th>
                    <th>Stock</th>
                    <th>Unit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td><span class="badge badge-light">{{ $product->sku }}</span></td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td>
                        <span class="badge badge-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td>{{ $product->unit }}</td>
                                    <td class="text-center">
                        <a href="{{ route('product.edit', $product->id) }}"
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('product.destroy', $product->id) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Yakin hapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Belum ada product
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
