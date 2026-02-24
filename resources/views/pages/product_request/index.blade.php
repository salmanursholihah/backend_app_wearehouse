@extends('layouts.app')

@section('content')

<div class="container">

    <h3>Product Request Approval</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        @foreach($requests as $r)
            <tr>
                <td>{{ $r->user->name ?? '-' }}</td>
                <td>{{ $r->product->name ?? '-' }}</td>
                <td>{{ $r->qty }}</td>
                <td>{{ $r->status }}</td>
                <td>

                    <form method="POST" action="{{ route('superadmin.product.approve',$r->id) }}" style="display:inline">
                        @csrf
                        <button class="btn btn-success btn-sm">Approve</button>
                    </form>

                    <form method="POST" action="{{ route('superadmin.product.reject',$r->id) }}" style="display:inline">
                        @csrf
                        <button class="btn btn-danger btn-sm">Reject</button>
                    </form>

                </td>
            </tr>
        @endforeach
        </tbody>

    </table>

</div>

@endsection
