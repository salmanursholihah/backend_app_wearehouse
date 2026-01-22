<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ChatWebController extends Controller
{
    /**
     * LIST CHAT ROOM USER LOGIN
     */

    public function index()
{
    $users = User::where('id', '!=', auth()->id())
        ->where('is_active', true)
        ->get();

    $rooms = ChatRoom::whereHas('users', function ($q) {
        $q->where('users.id', auth()->id());
    })->with('messages')->get();

    return view('pages.chat.index', compact('users', 'rooms'));
}
    /**
     * DETAIL CHAT ROOM
     */
    public function show(ChatRoom $room)
    {
        // SECURITY CHECK
        abort_if(
            !$room->participants->contains(Auth::id()),
            403
        );

        $messages = $room->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        return view('pages.chat.show', compact('room', 'messages'));
    }

    /**
     * SEND MESSAGE
     */
    public function send(ChatRoom $room)
    {
        abort_if(
            !$room->participants->contains(Auth::id()),
            403
        );

        request()->validate([
            'message' => 'required|string|max:1000'
        ]);

        $room->messages()->create([
            'sender_id' => Auth::id(),
            'message'   => request('message')
        ]);

        return redirect()->route('chat.show', $room->id);
    }
}
