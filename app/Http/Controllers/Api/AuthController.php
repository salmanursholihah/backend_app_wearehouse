<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;

// class AuthController extends Controller
// {
// public function login(Request $request)
//     {
//         $request->validate([
//             'email' => 'required|email',
//             'password' => 'required'
//         ]);

//         $user = User::where('email', $request->email)->first();

//         if (!$user) {
//             return response()->json([
//                 'message' => 'User tidak ditemukan'
//             ], 401);
//         }

//         if (!Hash::check($request->password, $user->password)) {
//             return response()->json([
//                 'message' => 'Password salah'
//             ], 401);
//         }

//         // WAJIB karena sudah pakai HasApiTokens
//         $token = $user->createToken('auth_token')->plainTextToken;

//         return response()->json([
//             'message' => 'Login berhasil',
//             'token' => $token,
//             'user' => [
//                 'id' => $user->id,
//                 'name' => $user->name,
//                 'email' => $user->email,
//                 'role' => $user->role
//             ]
//         ]);
//     }

//     public function register(Request $request)
// {
//     $request->validate([
//         'name' => 'required',
//         'email' => 'required|email|unique:users',
//         'password' => 'required|min:6',
//         'role' => 'in:user,admin,super_admin'
//     ]);

//     $user = User::create([
//         'name' => $request->name,
//         'email' => $request->email,
//         'password' => bcrypt($request->password),
//         'role' => $request->role ?? 'user'
//     ]);

//     return response()->json([
//         'message' => 'register success',
//         'user' => $user
//     ]);
// }

// }


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);

        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json(['message' => 'Login gagal'], 401);
        }

        $user = Auth::user();

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
