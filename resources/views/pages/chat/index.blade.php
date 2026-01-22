@extends('layouts.app')
@section('title','Chat')

@section('content')

<div class="section-header">
    <h1>Chat</h1>
</div>

<div class="row">
    {{-- USER LIST --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Mulai Chat</h4>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($users as $user)
                        <li class="list-group-item">
                            <a href="{{ route('pages.chat.start', $user->id) }}">
                                <i class="fas fa-user"></i>
                                {{ $user->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h4>Chat Terakhir</h4>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($rooms as $room)
                        <li class="list-group-item">
                            <a href="{{ route('pages.chat.room', $room->id) }}">
                                {{ $room->name }}
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">
                            Belum ada chat
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- CHAT DETAIL --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center text-muted">
                Pilih user untuk mulai chat
            </div>
        </div>
    </div>
</div>

@endsection
