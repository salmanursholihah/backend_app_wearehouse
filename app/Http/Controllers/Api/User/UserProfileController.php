<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
     /**
     * GET /api/user/profile
     * Ambil profil user login
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'phone'     => $user->phone,
                'address'   => $user->address,
                'image'     => $user->image,
                'is_active' => (bool) $user->is_active,
                'created_at'=> $user->created_at,
            ]
        ]);
    }

    /**
     * PUT /api/user/profile
     * Update profil (tanpa email & role)
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ]);

        $user->update([
            'name'    => $data['name'],
            'phone'   => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $user
        ]);
    }

    /**
     * POST /api/user/profile/image
     * Upload / Update foto profile
     */
    public function updateImage(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048']
        ]);

        // Hapus gambar lama jika ada
        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        // Simpan gambar baru
        $path = $request->file('image')->store('profile_images', 'public');

        $user->update([
            'image' => $path
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'data' => [
                'image' => $path,
                'image_url' => asset('storage/' . $path)
            ]
        ]);
    }
}
