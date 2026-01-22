@extends('layouts.app')
@section('title','Request Barang')

@section('content')
<div class="section-header">
    <h1>Request Barang</h1>
</div>

<div class="card">
    <div class="card-header">
        <h4>Daftar Pengajuan Barang</h4>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Product</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td>{{ $req->user->name }}</td>
                    <td>
                        <ul class="mb-0">
                            @foreach($req->items as $item)
                            <li>{{ $item->product->name }} ({{ $item->qty }})</li>
                            @endforeach
                        </ul>
                    </td>

                    <td>{{ $req->qty }}</td>
                    <td>
                        <span class="badge badge-{{ 
                            $req->status == 'approved' ? 'success' : 
                            ($req->status == 'rejected' ? 'danger' : 'warning') 
                        }}">
                            {{ ucfirst($req->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($req->status == 'pending')
                        <form action="{{ route('request.approve', $req->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>

                        <form action="{{ route('request.reject', $req->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Tidak ada request
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection