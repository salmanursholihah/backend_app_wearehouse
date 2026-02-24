<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\RoleApprovedNotification;
use App\Models\ActivityLog;



class RoleApprovalController extends Controller
{
    // public function index()
    // {
    //     $this->superAdminOnly();

    //     return RoleRequest::with('user')
    //         ->where('status', 'pending')
    //         ->latest()
    //         ->get();
    // }

    // public function approve($id)
    // {
    //     $this->superAdminOnly();

    //     $roleRequest = RoleRequest::findOrFail($id);

    //     $roleRequest->update([
    //         'status' => 'approved',
    //         'reviewed_at' => now(),
    //         // ✅ ID SUPER ADMIN
    //         'reviewed_by' => request()->user()->id,
    //     ]);

    //     // ✅ UPDATE USER YANG DIREQUEST
    //     $roleRequest->user()->update([
    //         'role' => 'admin'
    //     ]);

    //     return response()->json([
    //         'message' => 'User berhasil menjadi admin'
    //     ]);
    // }

    // public function reject($id)
    // {
    //     $this->superAdminOnly();

    //     $roleRequest = RoleRequest::findOrFail($id);

    //     $roleRequest->update([
    //         'status' => 'rejected',
    //         'reviewed_at' => now(),
    //         'reviewed_by' => request()->user()->id,
    //     ]);

    //     return response()->json([
    //         'message' => 'Request admin ditolak'
    //     ]);
    // }

    // private function superAdminOnly(): void
    // {
    //     $user = request()->user();

    //     abort_if(
    //         !$user || $user->role !== 'super_admin',
    //         403
    //     );
    // }
    // public function requestAdmin()
    // {
    //     $user = request()->user();

    //     // cek sudah pernah request
    //     $exists = RoleRequest::where('user_id', $user->id)
    //         ->where('status', 'pending')
    //         ->exists();

    //     if ($exists) {
    //         return response()->json([
    //             'message' => 'Kamu sudah pernah request admin'
    //         ], 400);
    //     }

    //     RoleRequest::create([
    //         'user_id' => $user->id,
    //         'status' => 'pending'
    //     ]);

    //     return response()->json([
    //         'message' => 'Request admin berhasil dikirim'
    //     ]);
    // }

    /*
    |--------------------------------------------------------------------------
    | LIST ALL ROLE REQUEST
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $requests = RoleRequest::with('user')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */
    public function approve($id)
    {
        DB::beginTransaction();

        try {

            $roleRequest = RoleRequest::where('id', $id)
                ->where('status', 'pending')
                ->first();

            if (!$roleRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request tidak ditemukan atau sudah diproses'
                ], 404);
            }

            $user = User::find($roleRequest->user_id);

            $user->update([
                'role' => $roleRequest->requested_role
            ]);

            $user->tokens()->delete();

            $user->notify(new RoleApprovedNotification($roleRequest->requested_role));

             ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'role_changed',
                'description' => "Role diubah menjadi {$roleRequest->requested_role} oleh user ID " . auth()->id()
            ]);

            $roleRequest->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil di-approve'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */
    public function reject($id)
    {
        $roleRequest = RoleRequest::where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$roleRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan atau sudah diproses'
            ], 404);
        }

        $roleRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role request di-reject'
        ]);
    }
}
