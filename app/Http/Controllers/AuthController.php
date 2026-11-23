<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'Satpam'
                ? redirect()->route('dashboard')
                : redirect()->route('mahasiswa.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return Auth::user()->role === 'Satpam'
                ? redirect()->intended(route('dashboard'))
                : redirect()->intended(route('mahasiswa.index'));
        }

        return back()->withErrors([
            'login' => 'Username atau password yang Anda masukkan salah. Silakan periksa kembali.',
        ])->onlyInput('username');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'Satpam'
                ? redirect()->route('dashboard')
                : redirect()->route('mahasiswa.index');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:4|max:50|unique:users,username',
            'nama' => 'required|string|min:3|max:100',
            'identitas' => 'nullable|string|max:100',
            'kontak' => 'required|string|min:9|max:30',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username tersebut sudah digunakan oleh akun lain.',
            'username.min' => 'Username minimal 4 karakter.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'kontak.required' => 'Nomor kontak wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'nama' => $validated['nama'],
            'identitas' => $validated['identitas'] ?? '',
            'kontak' => $validated['kontak'],
            'password' => Hash::make($validated['password']),
            'role' => 'Mahasiswa',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Registrasi berhasil! Selamat datang di TitikTemu.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('sukses', 'Anda telah berhasil keluar dari sistem.');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('profil.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:100',
            'identitas' => 'nullable|string|max:100',
            'kontak' => 'nullable|string|max:30',
            'password_baru' => 'nullable|string|min:6',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.min' => 'Nama lengkap minimal 3 karakter.',
            'password_baru.min' => 'Password baru minimal 6 karakter.',
        ]);

        $user->nama = $validated['nama'];
        $user->identitas = $validated['identitas'] ?? $user->identitas;
        $user->kontak = $validated['kontak'] ?? $user->kontak;

        if (!empty($validated['password_baru'])) {
            $user->password = Hash::make($validated['password_baru']);
        }

        $user->save();

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }
}
