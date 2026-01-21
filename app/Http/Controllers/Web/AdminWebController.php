<?php

// namespace App\Http\Controllers\Web;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use Illuminate\Http\Request;

// class AdminManagementController extends Controller
// {
//     public function index()
//     {
//         $this->authorizeSuper();

//         return view('pages.admins.index', [
//             'admins' => User::whereIn('role',['admin','super_admin'])->get()
//         ]);
//     }

//     public function store(Request $request)
//     {
//         $this->authorizeSuper();

//         $request->validate([
//             'name'     => 'required',
//             'email'    => 'required|email|unique:users',
//             'password' => 'required|min:6'
//         ]);

//         User::create([
//             'name'      => $request->name,
//             'email'     => $request->email,
//             'password'  => bcrypt($request->password),
//             'role'      => 'admin',
//             'is_active' => true
//         ]);

//         return back()->with('success','Admin berhasil ditambahkan');
//     }

//     public function deactivate(User $user)
//     {
//         $this->authorizeSuper();

//         abort_if($user->role === 'super_admin', 403);

//         $user->update([
//             'is_active' => false
//         ]);

//         return back();
//     }

//     private function authorizeSuper()
//     {
//         abort_if(auth()->user()->role !== 'super_admin', 403);
//     }
// }


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminWebController extends Controller
{
    public function index()
    {
        $this->superAdmin();

        return view('pages.admins.index', [
            'admins'=>User::whereIn('role',['admin','super_admin'])->get()
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $this->superAdmin();
        $user->update(['role'=>$request->role]);
        return back();
    }

    private function superAdmin()
    {
        abort_if(auth()->user()->role !== 'super_admin',403);
    }
}
