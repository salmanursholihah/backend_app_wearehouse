<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use Illuminate\Http\Request;

class RoleApprovalWebController extends Controller
{
    public function index()
    {
        $this->superAdminOnly();

        $requests = RoleRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('pages.role_request.index', compact('requests'));
    }

    public function approve($id)
    {
        $this->superAdminOnly();

        $roleRequest = RoleRequest::findOrFail($id);

        $roleRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => request()->user()->id,
        ]);

        $roleRequest->user()->update([
            'role' => 'admin'
        ]);

        return redirect()
            ->back()
            ->with('success', 'User berhasil di-approve menjadi admin');
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

        return redirect()
            ->back()
            ->with('error', 'Request admin ditolak');
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

