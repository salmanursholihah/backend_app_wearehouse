@extends('layouts.app')

@section('title', 'Approval Admin')

@section('content')
<div class="container mt-4">

    <h4 class="mb-3">Request Admin</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">

            @if($requests->isEmpty())
                <p class="text-muted text-center mb-0">
                    Tidak ada request admin
                </p>
            @else
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal Request</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->user->name }}</td>
                            <td>{{ $item->user->email }}</td>
                            <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                            <td class="text-center">

                                <form action="{{ route('super_admin.role_requests.approve', $item->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm"
                                            onclick="return confirm('Approve user ini menjadi admin?')">
                                        Approve
                                    </button>
                                </form>

                                <form action="{{ route('super_admin.role_requests.reject', $item->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Tolak request admin ini?')">
                                        Reject
                                    </button>
                                </form>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    </div>
</div>
@endsection
