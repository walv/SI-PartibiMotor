<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:40',
                'unique:users',
                'regex:/^[a-z0-9_]+$/'
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:kasir'], // bagian validasi role
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'username' => strtolower($data['username']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'], // bagian menyimpan role
        ]);
    }
    public function showRegistrationForm()
    {
        // Pastikan hanya admin yang bisa akses
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')
                   ->with('error', 'Akses ditolak! Hanya admin yang dapat registrasi user.');
        }

        return view('auth.register');
    }

    // Override method register untuk validasi tambahan
    public function register(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')
                   ->with('error', 'Akses ditolak! Hanya admin yang dapat registrasi user.');
        }

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        return redirect($this->redirectPath())
               ->with('success', 'User baru berhasil didaftarkan!');
    }
}
