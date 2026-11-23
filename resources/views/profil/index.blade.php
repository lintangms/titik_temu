@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="content-card" style="max-width: 680px;">
    <div style="margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: var(--slate-900);">Informasi Akun Pengguna</h3>
        <p style="font-size: 13px; color: var(--slate-500); margin-top: 4px;">Kelola informasi profil dan pengaturan keamanan akun Anda</p>
    </div>

    <form method="POST" action="{{ route('profil.update') }}">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label for="prof-username">Username</label>
                <input type="text" id="prof-username" value="{{ $user->username }}" disabled style="background: var(--slate-100); color: var(--slate-500); cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label for="prof-role">Peran / Hak Akses</label>
                <input type="text" id="prof-role" value="{{ $user->role }}" disabled style="background: var(--slate-100); color: var(--slate-500); cursor: not-allowed;">
            </div>
        </div>

        <div class="form-group">
            <label for="prof-nama">Nama Lengkap *</label>
            <input type="text" id="prof-nama" name="nama" required value="{{ old('nama', $user->nama) }}">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prof-identitas">No. Identitas (NIM / NIP)</label>
                <input type="text" id="prof-identitas" name="identitas" value="{{ old('identitas', $user->identitas) }}">
            </div>

            <div class="form-group">
                <label for="prof-kontak">Kontak HP / WhatsApp</label>
                <input type="text" id="prof-kontak" name="kontak" value="{{ old('kontak', $user->kontak) }}">
            </div>
        </div>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--slate-200);">
            <h4 style="font-size: 14px; font-weight: 600; color: var(--slate-800); margin-bottom: 8px;">Ganti Password (Opsional)</h4>
            <p style="font-size: 12.5px; color: var(--slate-500); margin-bottom: 14px;">Kosongkan jika Anda tidak ingin mengubah password akun.</p>

            <div class="form-group">
                <label for="prof-password">Password Baru</label>
                <input type="password" id="prof-password" name="password_baru" placeholder="Minimal 6 karakter">
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
