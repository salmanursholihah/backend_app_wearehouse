<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserWebController extends Controller
{
    /**
     * List user (hanya role user)
     */
    public function index()
    {
        $this->superAdmin();

        return view('pages.users.index', [
            'users' => User::where('role', 'user')->latest()->get()
        ]);
    }

    /**
     * Update role user
     */
    public function updateRole(Request $request, User $user)
    {
        $this->superAdmin();

        $request->validate([
            'role' => 'required|in:user,admin,super_admin'
        ]);

        $user->update([
            'role' => $request->role
        ]);

        return back()->with('success', 'Role user berhasil diubah');
    }

    /**
     * Toggle status aktif / nonaktif user
     */
    public function toggle(User $user)
    {
        $this->superAdmin();

        $user->update([
            'is_active' => ! $user->is_active
        ]);

        return back()->with('success', 'Status user berhasil diubah');
    }

    /**
     * Guard khusus super admin
     */
    private function superAdmin()
    {
        abort_if(
            !auth()->check() || auth()->user()->role !== 'super_admin',
            403
        );
    }
}
