@extends('layouts.app')
@section('title','Manajemen Admin')

@section('content')
<div class="section-header">
    <h1>Manajemen Admin</h1>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered">
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
            @foreach($admins as $admin)
            <tr>
                <td>{{ $admin->name }}</td>
                <td>{{ $admin->email }}</td>
                <td>{{ $admin->role }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection
