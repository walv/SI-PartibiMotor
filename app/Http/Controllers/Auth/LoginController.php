<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // Default redirect jika tidak override method `authenticated()`
    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        // Middleware 'guest' agar hanya user yang belum login bisa mengakses login
        // 'except' di sini berarti: logout boleh diakses meskipun sudah login
        $this->middleware('guest')->except('logout');
    }

    /**
     * Method ini dipanggil setelah user berhasil login.
     * Bisa digunakan untuk redirect sesuai role user.
     */
    protected function authenticated(Request $request, $user)
    {
        // Redirect berdasarkan role
        switch ($user->role) {
            case 'admin':
                return redirect()->route('dashboard');
            case 'kasir':
                return redirect()->route('sales.index');
            default:
                // Jika role tidak dikenali, logout dan kembalikan ke login dengan pesan error
                auth()->logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Akun tidak memiliki akses yang valid.'
                ]);
        }
    }
}
