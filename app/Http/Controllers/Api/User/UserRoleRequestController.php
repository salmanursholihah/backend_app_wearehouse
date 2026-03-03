<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use Illuminate\Http\Request;

class UserRoleRequestController extends Controller
{
        /**
     * POST /api/user/role-requests
     * User minta jadi admin
     *
     * body:
     * {
     *   "requested_role": "admin",
     *   "note": "alasan / keterangan"
     * }
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Jika user sudah admin/super_admin, tidak boleh request lagi
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Role Anda sudah admin/super_admin.',
            ], 422);
        }

        $data = $request->validate([
            'requested_role' => ['required', 'in:admin'], // user hanya boleh minta admin
            'note'           => ['nullable', 'string', 'max:500'],
        ]);

        // Cegah request ganda saat masih pending
        $hasPending = RoleRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki pengajuan role yang pending.',
            ], 422);
        }

        $roleRequest = RoleRequest::create([
            'user_id'         => $user->id,
            'requested_role'  => $data['requested_role'],
            'note'            => $data['note'] ?? null,
            'status'          => 'pending',
            'processed_by'    => null,
            'processed_at'    => null,
            'reason'          => null, // alasan reject (opsional, sesuai kolom)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan role berhasil dibuat.',
            'data'    => $roleRequest,
        ], 201);
    }

    /**
     * GET /api/user/role-requests
     * History role requests milik user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->query('per_page', 10);

        $list = RoleRequest::where('user_id', $user->id)
            ->with(['processedBy:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    /**
     * GET /api/user/role-requests/{id}
     * Detail role request milik user
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $roleRequest = RoleRequest::where('user_id', $user->id)
            ->with(['processedBy:id,name,email'])
            ->find($id);

        if (!$roleRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Role request tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $roleRequest,
        ]);
    }
}
