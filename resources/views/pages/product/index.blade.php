@extends('layouts.app')
@section('title','Approval Product')

@section('content')
<div class="section-header">
    <h1>Approval Product</h1>
</div>

<div class="card">
    <div class="card-body table-responsive">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama</th>
                    <th>Stock</th>
                    <th>Admin</th>
                    <th>Image</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->creator->name }}</td>

                    <td>
                        @foreach($product->images as $img)
                            <img src="{{ asset('storage/'.$img->image) }}"
                                 width="60"
                                 class="rounded mb-1">
                        @endforeach
                    </td>

                    <td>
                        <form method="POST"
                              action="{{ route('product.approve', $product->id) }}"
                              class="d-inline">
                            @csrf
                            <button class="btn btn-success btn-sm">
                                Approve
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('product.reject', $product->id) }}"
                              class="d-inline">
                            @csrf
                            <button class="btn btn-danger btn-sm">
                                Reject
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </div>
</div>
@endsection
