<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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


    public function create()
{
    return view('pages.users.create');
}

public function edit(User $user)
{
    return view('pages.users.edit', compact('user'));
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'role' => 'required',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'is_active' => true,
    ]);

    return redirect()->route('users.index');
}

public function update(Request $request, User $user)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'role' => 'required',
    ]);

    $data = $request->only('name', 'email', 'role');

    if ($request->password) {
        $data['password'] = Hash::make($request->password);
    }

    $user->update($data);

    return redirect()->route('users.index');
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
