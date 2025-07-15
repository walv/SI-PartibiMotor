<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:kasir',
            
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->has('is_active') // Boolean
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:kasir',
        ]);

        $userData = [
            
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    
    public function destroy(User $user)
    {
        // cegah penghapusan sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function changePassword()
{
    return view('users.change-password');
}

/**
 * Update the user's password.
 */
public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required|current_password',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::find(auth()->id());
    $user->password = Hash::make($request->password);
    $user->save();

    return redirect()->route('change.password')
        ->with('success', 'Password berhasil diubah.');
}
    public function deactivate(User $user)
{
    if ($user->id === auth()->id()) {
        return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
    }

    $user->delete(); // Ini akan soft delete jika menggunakan SoftDeletes

    return back()->with('success', 'Akun kasir berhasil dinonaktifkan.');
}
   public function trashed()
{
    $users = User::onlyTrashed()->paginate(10);
    return view('users.trashed', compact('users'));
}

// Method untuk mengaktifkan kembali akun
public function restore($id)
{
    $user = User::onlyTrashed()->findOrFail($id);
    $user->restore();

    return redirect()->route('users.trashed')
        ->with('success', 'Akun berhasil diaktifkan kembali.');
}

// Method untuk menghapus permanen
public function forceDelete($id)
{
    $user = User::onlyTrashed()->findOrFail($id);
    $user->forceDelete();

    return redirect()->route('users.trashed')
        ->with('success', 'Akun berhasil dihapus permanen.');
}
}

