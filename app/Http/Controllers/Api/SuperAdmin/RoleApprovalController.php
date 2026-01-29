<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use Illuminate\Http\Request;

class RoleApprovalController extends Controller
{
    public function index()
    {
        $this->superAdminOnly();

        return RoleRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function approve($id)
    {
        $this->superAdminOnly();

        $roleRequest = RoleRequest::findOrFail($id);

        $roleRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            // ✅ ID SUPER ADMIN
            'reviewed_by' => request()->user()->id,
        ]);

        // ✅ UPDATE USER YANG DIREQUEST
        $roleRequest->user()->update([
            'role' => 'admin'
        ]);

        return response()->json([
            'message' => 'User berhasil menjadi admin'
        ]);
    }

    public function reject($id)
    {
        $this->superAdminOnly();

        $roleRequest = RoleRequest::findOrFail($id);

        $roleRequest->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => request()->user()->id,
        ]);

        return response()->json([
            'message' => 'Request admin ditolak'
        ]);
    }

    private function superAdminOnly(): void
    {
        $user = request()->user();

        abort_if(
            !$user || $user->role !== 'super_admin',
            403
        );
    }
}
