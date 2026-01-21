<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;

class ChatWebController extends Controller
{
    public function index()
    {
        return view('pages.chat.index', [
            'rooms'=>ChatRoom::with('participants')->get()
        ]);
    }
}

