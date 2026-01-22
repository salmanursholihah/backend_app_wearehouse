<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $guarded = [];
    protected $fillable = ['name'];

    /**
     * 1 ChatRoom punya banyak ChatMessage
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_room_id');
    }

    /**
     * Ambil participant dari chat_messages (TANPA pivot)
     */
    public function participants()
    {
        return $this->hasManyThrough(
            User::class,
            ChatMessage::class,
            'chat_room_id', // FK di chat_messages
            'id',           // PK di users
            'id',           // PK di chat_rooms
            'sender_id'     // FK user di chat_messages
        )->distinct();
    }

    public function users(){
        return $this->hasMany(User::class);
    }
}
