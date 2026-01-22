@extends('layouts.app')
@section('title','Tambah User')

@section('content')

<div class="section-header">
    <h1>Tambah User</h1>
</div>

<div class="card">
    <div class="card-header">
        <h4>Form Tambah User</h4>
    </div>

    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>

            <div class="text-right">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
                <button class="btn btn-primary">
                    Simpan
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
