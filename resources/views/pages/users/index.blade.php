@extends('layouts.app')
@section('title','Manajemen User')

@section('content')

<div class="section-header d-flex justify-content-between align-items-center">
    <h1>Manajemen User</h1>

    {{-- TOMBOL TAMBAH --}}
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Tambah User
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h4>Daftar User</h4>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge badge-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="text-center">

                        {{-- EDIT --}}
                        <a href="{{ route('users.edit', $user->id) }}"
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>

                        {{-- TOGGLE STATUS --}}
                        <form action="{{ route('users.toggle', $user->id) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Ubah status user?')">
                            @csrf
                            <button class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Belum ada user
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
