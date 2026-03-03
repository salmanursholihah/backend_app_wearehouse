<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * GET /api/admin/users
     * List semua user (kecuali super_admin)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        $users = User::where('role', '!=', 'super_admin')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * GET /api/admin/users/{id}
     */
    public function show($id)
    {
        $user = User::where('role', '!=', 'super_admin')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * PUT /api/admin/users/{id}
     * Update data basic (tanpa ubah role & email)
     */
    public function update(Request $request, $id)
    {
        $user = User::where('role', '!=', 'super_admin')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'image'   => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name'    => $data['name'],
            'phone'   => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'image'   => $data['image'] ?? $user->image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui.',
            'data' => $user,
        ]);
    }

    /**
     * PUT /api/admin/users/{id}/activate
     */
    public function activate($id)
    {
        $user = User::where('role', '!=', 'super_admin')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $user->update([
            'is_active' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diaktifkan.',
            'data' => $user,
        ]);
    }

    /**
     * PUT /api/admin/users/{id}/deactivate
     */
    public function deactivate($id)
    {
        $user = User::where('role', '!=', 'super_admin')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $user->update([
            'is_active' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dinonaktifkan.',
            'data' => $user,
        ]);
    }
}
