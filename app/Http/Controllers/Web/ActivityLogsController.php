<?php

// namespace App\Http\Controllers\Web;

// use App\Http\Controllers\Controller;
// use App\Models\ActivityLog;

// class ActivityLogWebController extends Controller
// {
//     public function index()
//     {
//         $this->authorizeSuper();

//         return view('pages.logs.activity', [
//             'logs' => ActivityLog::with('user')
//                         ->latest()
//                         ->get()
//         ]);
//     }

//     private function authorizeSuper()
//     {
//         abort_if(auth()->user()->role !== 'super_admin', 403);
//     }
// }

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class ActivityLogWebController extends Controller
{
    public function index()
    {
        $this->superAdmin();

        return view('pages.activity_logs.index', [
            'logs'=>ActivityLog::with('user')->latest()->paginate(20)
        ]);
    }

    private function superAdmin()
    {
        abort_if(auth()->user()->role !== 'super_admin',403);
    }
}

