@extends('layouts.app')

@section('content')
<h3>Request Report</h3>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Status</th>
            <th>Processed By</th>
        </tr>
    </thead>
    <tbody>
        @foreach($requests as $req)
        <tr>
            <td>#{{ $req->id }}</td>
            <td>{{ $req->user->name }}</td>
            <td>{{ $req->status }}</td>
            <td>{{ optional($req->processor)->name }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
