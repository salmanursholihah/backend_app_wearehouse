<!-- @extends('layouts.app')

@section('content')
<h3>Stock Report</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Product</th>
            <th>Type</th>
            <th>Qty</th>
            <th>User</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>{{ $log->product->name }}</td>
            <td>{{ $log->type }}</td>
            <td>{{ $log->qty }}</td>
            <td>{{ $log->user->name }}</td>
            <td>{{ $log->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection -->


@extends('layouts.app')
@section('title','Laporan Stok')

@section('content')
<div class="section-header">
    <h1>Laporan Stok Gudang</h1>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <tr>
                <th>Produk</th>
                <th>Perubahan</th>
                <th>User</th>
                <th>Tanggal</th>
            </tr>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->product->name }}</td>
                <td>{{ $log->quantity }}</td>
                <td>{{ $log->user->name }}</td>
                <td>{{ $log->created_at }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
