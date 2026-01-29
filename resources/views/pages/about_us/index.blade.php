@extends('layouts.app')

@section('content')
<div class="container">
    <h3>About Us</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Title</label>
            <input class="form-control" name="title"
                value="{{ $about->title ?? '' }}">
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea class="form-control"
                name="description">{{ $about->description ?? '' }}</textarea>
        </div>

        <div class="mb-3">
            <label>Image</label>
            <input type="file" class="form-control" name="image">
        </div>

        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
