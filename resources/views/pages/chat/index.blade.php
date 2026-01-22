@extends('layouts.app')
@section('title','Chat')

@section('content')
<div class="section-header">
    <h1>Chat Room</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4>Daftar Chat</h4>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($rooms as $room)
                        <li class="list-group-item">
                            <a href="{{ route('chat.index', $room->id) }}">
                                <strong>{{ $room->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $room->messages->last()->created_at->diffForHumans() ?? '' }}
                                </small>
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">
                            Belum ada chat
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center text-muted">
                Pilih chat untuk memulai percakapan
            </div>
        </div>
    </div>
</div>
@endsection
