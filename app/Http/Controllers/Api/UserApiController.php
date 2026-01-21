<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class UserApiController extends Controller
// {
//     public function index()
//     {
//         $this->authorizeSuper();

//         return response()->json([
//             'data' => User::where('role', 'user')->latest()->get()
//         ]);
//     }

//     public function updateRole(Request $request, User $user)
//     {
//         $this->authorizeSuper();

//         $request->validate([
//             'role' => 'required|in:user,admin'
//         ]);

//         $user->update([
//             'role' => $request->role
//         ]);

//         return response()->json([
//             'message' => 'Role user berhasil diperbarui'
//         ]);
//     }

//     public function toggleStatus(User $user)
//     {
//         $this->authorizeSuper();

//         $user->update([
//             'is_active' => ! $user->is_active
//         ]);

//         return response()->json([
//             'message' => 'Status user berhasil diperbarui'
//         ]);
//     }

//     private function authorizeSuper()
//     {
//         if (Auth::user()->role !== 'super_admin') {
//             abort(403, 'Unauthorized');
//         }
//     }
// }


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function index()
    {
        $this->superOnly();
        return response()->json(User::latest()->get());
    }

    public function updateRole(Request $request, $id)
    {
        $this->superOnly();

        $request->validate([
            'role' => 'required|in:user,admin'
        ]);

        User::findOrFail($id)->update([
            'role' => $request->role
        ]);

        return response()->json(['message' => 'Role updated']);
    }

    private function superOnly()
    {
        abort_if(auth()->user()->role !== 'super_admin', 403);
    }
}
