@extends('layouts.app')
@section('title','Edit User')

@section('content')

<div class="section-header">
    <h1>Edit User</h1>
</div>

<div class="card">
    <div class="card-header">
        <h4>Form Edit User</h4>
    </div>

    <div class="card-body">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name"
                       class="form-control"
                       value="{{ $user->name }}" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                       class="form-control"
                       value="{{ $user->email }}" required>
            </div>

            <div class="form-group">
                <label>Password (Opsional)</label>
                <input type="password" name="password"
                       class="form-control"
                       placeholder="Kosongkan jika tidak diubah">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            <div class="text-right">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
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
