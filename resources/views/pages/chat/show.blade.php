@extends('layouts.app')

@section('title', 'Chat')

@section('content')

<div class="section-header">
    <h1>Chat</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item">
            {{ $room->display_name ?? 'Chat Room' }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">

        <div class="card chat-box card-success" id="mychatbox">

            {{-- HEADER --}}
            <div class="card-header">
                <h4>
                    <i class="fas fa-comments"></i>
                    {{ $room->display_name ?? 'Chat Room' }}
                </h4>
            </div>

            {{-- CHAT CONTENT --}}
            <div class="card-body chat-content"
                 style="height: 400px; overflow-y: auto;">

                @forelse($messages as $message)

                    {{-- MESSAGE FROM ME --}}
                    @if($message->sender_id === auth()->id())
                        <div class="chat-item chat-right">
                            <div class="chat-details">
                                <div class="chat-text">
                                    {{ $message->message }}
                                </div>
                                <div class="chat-time">
                                    {{ $message->created_at->format('H:i') }}
                                </div>
                            </div>
                        </div>

                    {{-- MESSAGE FROM OTHER --}}
                    @else
                        <div class="chat-item chat-left">
                            <div class="chat-details">
                                <div class="chat-name">
                                    {{ $message->sender->name }}
                                </div>
                                <div class="chat-text">
                                    {{ $message->message }}
                                </div>
                                <div class="chat-time">
                                    {{ $message->created_at->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    @endif

                @empty
                    <div class="text-center text-muted mt-5">
                        <i class="fas fa-comment-slash fa-2x mb-2"></i>
                        <p>Belum ada pesan</p>
                    </div>
                @endforelse

            </div>

            {{-- CHAT INPUT --}}
            <div class="card-footer chat-form">
                <form method="POST"
                      action="{{ route('chat.send', $room->id) }}"
                      class="d-flex align-items-center">
                    @csrf

                    <input type="text"
                           name="message"
                           class="form-control"
                           placeholder="Tulis pesan..."
                           required
                           autocomplete="off">

                    <button class="btn btn-success ml-2">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    // auto scroll ke bawah
    const chatBox = document.querySelector('.chat-content');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endpush
