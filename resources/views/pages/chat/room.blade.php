@extends('layouts.app')
@section('title','Chat')

@section('content')

<div class="section-header">
    <h1>{{ $room->name }}</h1>
</div>

<div class="card">
    <div class="card-body" style="height:400px; overflow-y:auto">
        @foreach($room->messages as $message)
            <div class="mb-2 {{ $message->user_id == auth()->id() ? 'text-right' : '' }}">
                <strong>{{ $message->user->name }}</strong>
                <div class="alert alert-{{ $message->user_id == auth()->id() ? 'primary' : 'secondary' }}">
                    {{ $message->message }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="card-footer">
        <form action="{{ route('pages.chat.send') }}" method="POST">
            @csrf
            <input type="hidden" name="room_id" value="{{ $room->id }}">
            <div class="input-group">
                <input type="text" name="message" class="form-control" required>
                <div class="input-group-append">
                    <button class="btn btn-primary">Kirim</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
