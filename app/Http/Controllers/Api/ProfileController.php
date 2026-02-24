<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

public function profile(Request $request)
{
    return response()->json($request->user());
}

// public function updateProfile(Request $request)
// {
//     $user = $request->user();

//     $validatedData = $request->validate([
//         'name' => 'sometimes|string|max:255',
//         'image' => 'sometimes|image|max:2048', // max 2MB
//     ]);

//     if ($request->hasFile('image')) {
//         $imagePath = $request->file('image')->store('profile_images', 'public');
//         $validatedData['image'] = $imagePath;
//     }

//     $user->update($validatedData);
//     $user->save();

//     return response()->json($user);

// }

/*
    |--------------------------------------------------------------------------
    | UPDATE PROFILE
    |--------------------------------------------------------------------------
    */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('profile_images', 'public');

            $data['image'] = $imagePath;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui',
            'data' => $user
        ]);
    }

    public function changePassword(Request $request)
{
    $user = auth()->user();

    $validator = Validator::make($request->all(), [
        'current_password' => 'required',
        'new_password' => 'required|min:6|confirmed'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Password lama salah'
        ], 400);
    }

    $user->update([
        'password' => Hash::make($request->new_password)
    ]);

    // Logout semua device setelah ganti password
    $user->tokens()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Password berhasil diubah, silakan login kembali'
    ]);
}
}
