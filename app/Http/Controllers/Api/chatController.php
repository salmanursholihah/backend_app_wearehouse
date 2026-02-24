<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\ChatMessage;

// class ChatController extends Controller
// {
//     // Kirim pesan
//     public function send(Request $request)
//     {
//         $request->validate([
//             'room_id' => 'required',
//             'message' => 'required|string'
//         ]);

//         $chat = ChatMessage::create([
//             'chat_room_id' => $request->room_id,
//             'sender_id' => auth()->id(),
//             'message' => $request->message
//         ]);

//         return response()->json($chat, 201);
//     }

//     // History chat per room
//     public function history($room)
//     {
//         return ChatMessage::where('chat_room_id', $room)
//             ->with('sender')
//             ->orderBy('created_at', 'asc')
//             ->get();
//     }
// }
