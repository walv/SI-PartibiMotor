<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    // Tampilkan profil
    public function show()
    {
        return view('account.show', ['user' => Auth::user()]);
    }

    // Form edit
    public function edit()
    {
        return view('account.edit', ['user' => Auth::user()]);
    }

    // Proses update
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
           
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            Log::info('File diterima. Nama: ' . $request->file('avatar')->getClientOriginalName());

            // Pastikan direktori ada
            if (!Storage::disk('public')->exists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars');
                Log::info('Direktori avatars dibuat');
            }

            // Hapus avatar lama jika ada dan file-nya benar-benar ada
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Simpan file baru
            try {
                $path = $request->file('avatar')->store('avatars', 'public');
                Log::info('File disimpan di: ' . $path);
                $user->avatar = $path;
            } catch (\Exception $e) {
                Log::error('Gagal menyimpan file: ' . $e->getMessage());
                return redirect()->back()->withErrors(['avatar' => 'Gagal upload gambar.']);
            }
        }

        // Update data user
        $user->name = $validated['name'];
      
        // Simpan perubahan

        if ($user->isDirty()) {
            $user->save();
            return redirect()->route('account.show')->with('success', 'Profil berhasil diperbarui!');
        }

        return redirect()->route('account.edit')->with('info', 'Tidak ada perubahan data.');
    }
}
