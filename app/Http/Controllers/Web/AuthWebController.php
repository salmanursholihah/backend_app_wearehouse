<?php

// namespace App\Http\Controllers\Web;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class AuthWebController extends Controller
// {
//     /**
//      * =====================
//      * FORM LOGIN
//      * =====================
//      */
//     public function showLogin()
//     {
//         if (Auth::check()) {
//             return $this->redirectByRole();
//         }

//         return view('auth.login');
//     }

//     /**
//      * =====================
//      * PROSES LOGIN
//      * =====================
//      */
//     public function login(Request $request)
//     {
//         $credentials = $request->validate([
//             'email'    => 'required|email',
//             'password' => 'required'
//         ]);

//         if (Auth::attempt($credentials)) {

//             $request->session()->regenerate();

//             // cek status user
//             if (!auth()->user()->is_active) {
//                 Auth::logout();
//                 return back()->withErrors([
//                     'email' => 'Akun Anda tidak aktif'
//                 ]);
//             }

//             return $this->redirectByRole();
//         }

//         return back()->withErrors([
//             'email' => 'Email atau password salah'
//         ]);
//     }

//     /**
//      * =====================
//      * LOGOUT
//      * =====================
//      */
//     public function logout(Request $request)
//     {
//         Auth::logout();

//         $request->session()->invalidate();
//         $request->session()->regenerateToken();

//         return redirect()->route('login');
//     }

//     /**
//      * =====================
//      * REDIRECT BY ROLE
//      * =====================
//      */
//     private function redirectByRole()
//     {
//         $role = auth()->user()->role;

//         return match ($role) {
//             'super_admin' => redirect()->route('dashboard'),
//             'admin'       => redirect()->route('admin.dashboard'),
//             default       => redirect()->route('user.dashboard'),
//         };
//     }
// }



namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthWebController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Login gagal']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        return redirect()->route('login');
    }
}
