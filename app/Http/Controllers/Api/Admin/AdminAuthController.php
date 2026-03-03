<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
//     /**
//      * POST /api/auth/admin/login
//      * Login khusus admin & super_admin
//      */
//     public function login(Request $request)
//     {
//         $data = $request->validate([
//             'email'    => ['required', 'email', 'max:150'],
//             'password' => ['required', 'string', 'min:6'],
//         ]);

//         $user = User::where('email', $data['email'])->first();

//         if (!$user || !Hash::check($data['password'], $user->password)) {
//             throw ValidationException::withMessages([
//                 'email' => ['Email atau password salah.'],
//             ]);
//         }

//         if ((int)$user->is_active !== 1) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Akun tidak aktif.',
//             ], 403);
//         }

//         // 🔐 Hanya admin & super_admin
//         if (!in_array($user->role, ['admin', 'super_admin'])) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Akun ini bukan untuk aplikasi Admin.',
//             ], 403);
//         }

//         // Hapus token lama
//         $user->tokens()->delete();

//         // Ability token
//         $abilities = $user->role === 'super_admin'
//             ? ['admin', 'super_admin']
//             : ['admin'];

//         $token = $user->createToken('admin-token', $abilities)->plainTextToken;

//         return response()->json([
//             'success' => true,
//             'message' => 'Login admin berhasil.',
//             'data' => [
//                 'token' => $token,
//                 'user'  => [
//                     'id'        => $user->id,
//                     'name'      => $user->name,
//                     'email'     => $user->email,
//                     'role'      => $user->role,
//                     'phone'     => $user->phone,
//                     'address'   => $user->address,
//                     'image'     => $user->image,
//                     'is_active' => (bool) $user->is_active,
//                 ],
//             ],
//         ]);
//     }

//     /**
//      * GET /api/auth/admin/me
//      */
//     public function me(Request $request)
//     {
//         return response()->json([
//             'success' => true,
//             'data' => $request->user(),
//         ]);
//     }

//     /**
//      * POST /api/auth/admin/logout
//      */
//     public function logout(Request $request)
//     {
//         $request->user()->currentAccessToken()?->delete();

//         return response()->json([
//             'success' => true,
//             'message' => 'Logout berhasil.',
//         ]);
//     }

//     /**
//      * POST /api/auth/admin/register
//      * Hanya super_admin (sudah dibatasi middleware role:super_admin)
//      */
//     public function register(Request $request)
//     {
//         $data = $request->validate([
//             'name'     => ['required', 'string', 'max:100'],
//             'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
//             'password' => ['required', 'string', 'min:6'],
//             'phone'    => ['nullable', 'string', 'max:30'],
//             'address'  => ['nullable', 'string'],
//             'role'     => ['nullable', 'in:admin,super_admin'],
//         ]);

//         $role = $data['role'] ?? 'admin';

//         $admin = User::create([
//             'name'      => $data['name'],
//             'email'     => $data['email'],
//             'password'  => Hash::make($data['password']),
//             'phone'     => $data['phone'] ?? null,
//             'address'   => $data['address'] ?? null,
//             'role'      => $role,
//             'is_active' => true,
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Admin berhasil dibuat.',
//             'data'    => $admin,
//         ], 201);
// }


//code 2
   /**
     * LOGIN - khusus role admin/super_admin
     * POST /api/auth/admin/login
     * body: { "email": "...", "password": "..." }
     */
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

        // Proteksi: admin app hanya untuk role admin/super_admin
        if (!in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini bukan untuk aplikasi Admin.',
            ], 403);
        }

        // Optional: hapus token lama
        $user->tokens()->delete();

        // Token khusus admin app (abilities opsional)
        $token = $user->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login admin berhasil.',
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
        ]);
    }

    /**
     * REGISTER - buat admin baru (hanya super_admin)
     * POST /api/auth/admin/register
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'address'  => ['nullable', 'string'],
            'image'    => ['nullable', 'string', 'max:255'], // path/url
            'role'     => ['nullable', 'in:admin,super_admin'], // default admin
        ]);

        $adminRole = $data['role'] ?? 'admin';

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $adminRole,
            'phone'     => $data['phone'] ?? null,
            'address'   => $data['address'] ?? null,
            'image'     => $data['image'] ?? null,
            'is_active' => true,
        ]);

        $user->tokens()->delete();

        $token = $user->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Register admin berhasil.',
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

    /**
     * ME - ambil admin login
     * GET /api/auth/admin/me
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
     * POST /api/auth/admin/logout
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
