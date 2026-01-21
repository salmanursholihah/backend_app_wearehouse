<?php

// namespace App\Http\Controllers\Web;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\User;

// class UserWebController extends Controller
// {
//     public function index()
//     {
        
//         $this->super();

//         return view('pages.users.index', [
//             'users' => User::all()
//         ]);
//     }

//     public function updateRole(Request $request, User $user)
//     {
//         $this->super();

//         $user->update([
//             'role' => $request->role
//         ]);

//         return back();
//     }

//     public function toggle(User $user)
//     {
//         $this->super();

//         $user->update([
//             'is_active' => ! $user->is_active
//         ]);

//         return back();
//     }

//     private function super()
//     {
//         abort_if(auth()->user()->role !== 'super_admin', 403);
//     }
// }


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserWebController extends Controller
{
    public function index()
    {
        $this->superAdmin();

        return view('pages.users.index', [
            'users' => User::where('role','user')->latest()->get()
        ]);
    }

    private function superAdmin()
    {
        abort_if(auth()->user()->role !== 'super_admin',403);
    }
}
