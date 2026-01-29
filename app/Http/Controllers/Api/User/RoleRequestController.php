<?php
namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use Illuminate\Http\Request;

class RoleRequestController extends Controller
{
    public function requestAdmin(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'user') {
            return response()->json([
                'message' => 'Role Anda tidak dapat mengajukan request'
            ], 403);
        }

        $exists = RoleRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Request admin masih pending'
            ], 409);
        }

        RoleRequest::create([
            'user_id' => $user->id,
            'requested_role' => 'admin'
        ]);

        return response()->json([
            'message' => 'Request admin berhasil dikirim'
        ]);
    }

}

