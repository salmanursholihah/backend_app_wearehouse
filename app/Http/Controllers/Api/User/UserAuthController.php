<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserAuthController extends Controller
{
    /**
     * LOGIN - khusus role user (Warehouse User App)
     * POST /api/auth/user/login
     * body: { "email": "...", "password": "..." }
     */

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'address'  => ['nullable', 'string'],
            'image'    => ['nullable', 'string', 'max:255'], // simpan path/URL (kalau upload nanti kita buat endpoint sendiri)
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'user',
            'phone'     => $data['phone'] ?? null,
            'address'   => $data['address'] ?? null,
            'image'     => $data['image'] ?? null,
            'is_active' => true,
        ]);

        // Optional: pastikan token lama ga ada (harusnya belum ada)
        $user->tokens()->delete();

        // Token khusus user app
        $token = $user->createToken('user-token', ['user'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Register user berhasil.',
            'data' => [
                'token' => $token,
                'user'  => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'role'      => $user->role,
                    'phone'     => $user->phone,
                    'address'   => $user->address,
                    'image'     => $user->image,
                    'is_active' => (bool) $user->is_active,
                    'created_at'=> $user->created_at,
                ],
            ],
        ], 201);
    }

    
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Cek aktif
        if ((int) $user->is_active !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        // Proteksi: user app hanya untuk role user
        if ($user->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini bukan untuk aplikasi User.',
            ], 403);
        }

        // Optional: hapus semua token lama (biar 1 token aktif saja)
        $user->tokens()->delete();

        // Token khusus user app (abilities opsional)
        $token = $user->createToken('user-token', ['user'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login user berhasil.',
            'data' => [
                'token' => $token,
                'user'  => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'role'      => $user->role,
                    'phone'     => $user->phone,
                    'address'   => $user->address,
                    'image'     => $user->image,
                    'is_active' => (bool) $user->is_active,
                    'created_at' => $user->created_at,
                ],
            ],
        ]);
    }

    /**
     * ME - ambil user login
     * GET /api/auth/user/me
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    /**
     * LOGOUT - hapus token aktif
     * POST /api/auth/user/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}

