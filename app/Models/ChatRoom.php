<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $guarded = [];
    protected $fillable = ['name'];



    public function users(){
        return $this->hasMany(User::class);
    }
     public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'chat_room_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_room_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'chat_room_id')->latestOfMany();
    }
}
