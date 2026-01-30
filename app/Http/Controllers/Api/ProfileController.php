<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{

public function profile(Request $request)
{
    return response()->json($request->user());
}

public function updateProfile(Request $request)
{
    $user = $request->user();

    $validatedData = $request->validate([
        'name' => 'sometimes|string|max:255',
        'image' => 'sometimes|image|max:2048', // max 2MB
    ]);

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('profile_images', 'public');
        $validatedData['image'] = $imagePath;
    }

    $user->update($validatedData);
    $user->save();

    return response()->json($user);

}
}
