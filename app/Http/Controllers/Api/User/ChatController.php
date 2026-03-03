<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
        public function rooms(Request $request)
    {
        $user = $request->user();

        $rooms = ChatRoom::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->with([
                'participants.user:id,name,email,image,role',
                'lastMessage.sender:id,name',
            ])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($room) use ($user) {
                // Tentukan "title" untuk personal chat (nama lawan chat)
                $room->display_title = $room->type === 'personal'
                    ? optional($room->participants->firstWhere('user_id', '!=', $user->id)?->user)->name
                    : ($room->name ?? 'Group');

                return $room;
            });

        return response()->json([
            'success' => true,
            'data' => $rooms,
        ]);
    }

    // /**
    //  * POST /api/user/chat/rooms
    //  * Buat room personal/group
    //  *
    //  * body personal:
    //  * { "type": "personal", "user_id": 5 }
    //  *
    //  * body group:
    //  * { "type": "group", "name": "Tim Gudang", "user_ids": [2,5,9] }
    //  */
    // public function createRoom(Request $request)
    // {
    //     $user = $request->user();

    //     $data = $request->validate([
    //         'type' => ['required', 'in:personal,group'],
    //         'name' => ['nullable', 'string', 'max:100'],
    //         'user_id' => ['nullable', 'integer'],        // untuk personal
    //         'user_ids' => ['nullable', 'array'],         // untuk group
    //         'user_ids.*' => ['integer'],
    //     ]);

    //     if ($data['type'] === 'personal') {
    //         if (empty($data['user_id'])) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'user_id wajib untuk personal chat.',
    //             ], 422);
    //         }

    //         if ((int)$data['user_id'] === (int)$user->id) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Tidak bisa membuat chat dengan diri sendiri.',
    //             ], 422);
    //         }

    //         $target = User::find($data['user_id']);
    //         if (!$target) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Target user tidak ditemukan.',
    //             ], 404);
    //         }

    //         // Cek apakah room personal sudah ada (antara 2 user ini)
    //         $existingRoom = ChatRoom::where('type', 'personal')
    //             ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
    //             ->whereHas('participants', fn($q) => $q->where('user_id', $target->id))
    //             ->first();

    //         if ($existingRoom) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Room personal sudah ada.',
    //                 'data' => $existingRoom->load([
    //                     'participants.user:id,name,email,image,role',
    //                     'lastMessage.sender:id,name',
    //                 ]),
    //             ]);
    //         }

    //         $room = DB::transaction(function () use ($user, $target) {
    //             $room = ChatRoom::create([
    //                 'type' => 'personal',
    //                 'name' => null,
    //                 'created_by' => $user->id,
    //             ]);

    //             ChatParticipant::insert([
    //                 ['chat_room_id' => $room->id, 'user_id' => $user->id,   'created_at' => now(), 'updated_at' => now()],
    //                 ['chat_room_id' => $room->id, 'user_id' => $target->id, 'created_at' => now(), 'updated_at' => now()],
    //             ]);

    //             return $room;
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Room personal berhasil dibuat.',
    //             'data' => $room->load(['participants.user:id,name,email,image,role']),
    //         ], 201);
    //     }

    //     // GROUP
    //     $memberIds = collect($data['user_ids'] ?? [])->map(fn($v) => (int)$v)->unique()->values();

    //     // Minimal group: 2 anggota tambahan atau 1 tambahan? (kebijakan)
    //     // Saya izinkan minimal 1 user lain.
    //     if ($memberIds->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'user_ids wajib untuk group chat.',
    //         ], 422);
    //     }

    //     // Jangan masukkan diri sendiri dua kali
    //     $allIds = $memberIds->push((int)$user->id)->unique()->values();

    //     // Pastikan semua user ada
    //     $countUsers = User::whereIn('id', $allIds)->count();
    //     if ($countUsers !== $allIds->count()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Ada user_ids yang tidak valid.',
    //         ], 422);
    //     }

    //     $room = DB::transaction(function () use ($user, $data, $allIds) {
    //         $room = ChatRoom::create([
    //             'type' => 'group',
    //             'name' => $data['name'] ?? 'Group',
    //             'created_by' => $user->id,
    //         ]);

    //         $rows = $allIds->map(fn($id) => [
    //             'chat_room_id' => $room->id,
    //             'user_id' => $id,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ])->toArray();

    //         ChatParticipant::insert($rows);

    //         return $room;
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Room group berhasil dibuat.',
    //         'data' => $room->load(['participants.user:id,name,email,image,role']),
    //     ], 201);
    // }


    //code 2
    // ChatController.php (potongan createRoom)
public function createRoom(Request $request)
{
    $me = $request->user();

    $data = $request->validate([
        'target' => ['required', 'string'], // email / username
        'type'   => ['nullable', 'in:personal,group'],
        'name'   => ['nullable', 'string', 'max:100'], // group only
    ]);

    // ✅ paling aman: target = email
    $targetUser = \App\Models\User::where('email', $data['target'])->first();

    if (!$targetUser) {
        return response()->json([
            'success' => false,
            'message' => 'User tujuan tidak ditemukan'
        ], 404);
    }

    if ($targetUser->id === $me->id) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak bisa chat ke diri sendiri'
        ], 422);
    }

    // ====== PERSONAL ROOM: cari dulu apakah sudah ada ======
    $room = \App\Models\ChatRoom::where('type', 'personal')
        ->whereHas('participants', fn($q) => $q->where('user_id', $me->id))
        ->whereHas('participants', fn($q) => $q->where('user_id', $targetUser->id))
        ->first();

    if (!$room) {
        $room = \App\Models\ChatRoom::create([
            'type' => 'personal',
            'name' => null,
        ]);

        \App\Models\ChatParticipant::insert([
            ['chat_room_id' => $room->id, 'user_id' => $me->id, 'created_at' => now(), 'updated_at' => now()],
            ['chat_room_id' => $room->id, 'user_id' => $targetUser->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    // TODO: return response room + participants + user names
    return response()->json([
        'success' => true,
        'message' => 'Room siap',
        'data' => $room->load('participants.user')
    ]);
}



    /**
     * GET /api/user/chat/rooms/{roomId}/messages
     * Ambil messages (paginate)
     */
    public function messages(Request $request, $roomId)
    {
        $user = $request->user();

        // Pastikan user peserta
        $isMember = ChatParticipant::where('chat_room_id', $roomId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak punya akses ke room ini.',
            ], 403);
        }

        $perPage = (int) $request->query('per_page', 20);

        $messages = ChatMessage::where('chat_room_id', $roomId)
            ->with(['sender:id,name,email,image'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * POST /api/user/chat/rooms/{roomId}/messages
     * Kirim pesan
     *
     * body: { "message": "halo" }
     */
    public function sendMessage(Request $request, $roomId)
    {
        $user = $request->user();

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Pastikan user peserta
        $isMember = ChatParticipant::where('chat_room_id', $roomId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak punya akses ke room ini.',
            ], 403);
        }

        $room = ChatRoom::find($roomId);
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan.',
            ], 404);
        }

        $msg = ChatMessage::create([
            'chat_room_id' => $roomId,
            'sender_id'    => $user->id,
            'message'      => $data['message'],
        ]);

        // Biar room naik ke atas list (updated_at)
        $room->touch();

        return response()->json([
            'success' => true,
            'message' => 'Pesan terkirim.',
            'data' => $msg->load(['sender:id,name,email,image']),
        ], 201);
    }

    /**
     * POST /api/user/chat/rooms/{roomId}/participants
     * Tambah participant (group only)
     *
     * body: { "user_id": 9 }
     */
    public function addParticipant(Request $request, $roomId)
    {
        $user = $request->user();

        $data = $request->validate([
            'user_id' => ['required', 'integer'],
        ]);

        $room = ChatRoom::find($roomId);
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan.',
            ], 404);
        }

        if ($room->type !== 'group') {
            return response()->json([
                'success' => false,
                'message' => 'Tambah participant hanya untuk group chat.',
            ], 422);
        }

        // Hanya owner (created_by) yang boleh add/remove
        if ((int)$room->created_by !== (int)$user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembuat group yang boleh menambah participant.',
            ], 403);
        }

        $target = User::find($data['user_id']);
        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $exists = ChatParticipant::where('chat_room_id', $roomId)
            ->where('user_id', $target->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => true,
                'message' => 'User sudah menjadi participant.',
            ]);
        }

        ChatParticipant::create([
            'chat_room_id' => $roomId,
            'user_id' => $target->id,
        ]);

        $room->touch();

        return response()->json([
            'success' => true,
            'message' => 'Participant berhasil ditambahkan.',
            'data' => $room->load(['participants.user:id,name,email,image,role']),
        ], 201);
    }

    /**
     * DELETE /api/user/chat/rooms/{roomId}/participants/{userId}
     * Hapus participant (group only)
     */
    public function removeParticipant(Request $request, $roomId, $userId)
    {
        $user = $request->user();

        $room = ChatRoom::find($roomId);
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan.',
            ], 404);
        }

        if ($room->type !== 'group') {
            return response()->json([
                'success' => false,
                'message' => 'Hapus participant hanya untuk group chat.',
            ], 422);
        }

        // Hanya owner (created_by)
        if ((int)$room->created_by !== (int)$user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembuat group yang boleh menghapus participant.',
            ], 403);
        }

        // Owner tidak boleh remove dirinya sendiri (opsional)
        if ((int)$userId === (int)$room->created_by) {
            return response()->json([
                'success' => false,
                'message' => 'Owner group tidak bisa dihapus.',
            ], 422);
        }

        $deleted = ChatParticipant::where('chat_room_id', $roomId)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Participant tidak ditemukan.',
            ], 404);
        }

        $room->touch();

        return response()->json([
            'success' => true,
            'message' => 'Participant berhasil dihapus.',
            'data' => $room->load(['participants.user:id,name,email,image,role']),
        ]);
    }

}
