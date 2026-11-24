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

<!-- Zona Bahaya: Hapus Akun Sendiri -->
<div class="content-card" style="max-width: 680px; margin-top: 20px; border-color: #fecaca; background: #fff5f5;">
    <div class="content-card-header" style="border-bottom: 1px solid #fee2e2; margin-bottom: 12px; padding-bottom: 8px;">
        <h3 style="color: #b91c1c; font-size: 15px; font-weight: 700;">Zona Bahaya: Hapus Akun</h3>
    </div>
    <p style="font-size: 13px; color: var(--slate-700); margin-bottom: 14px; line-height: 1.5;">
        Tindakan ini akan menghapus data akun profil Anda secara permanen dari sistem. Anda akan otomatis logout dan tidak dapat mengakses sistem dengan akun ini lagi.
    </p>
    <button type="button" class="btn-danger" onclick="bukaModal('modal-hapus-akun')">Hapus Akun Saya</button>
</div>

<!-- Modal Konfirmasi Hapus Akun -->
<div class="modal hidden" id="modal-hapus-akun">
    <div class="modal-content" style="max-width: 460px;">
        <div class="modal-header">
            <h3 style="color: #b91c1c;">Konfirmasi Hapus Akun</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-hapus-akun')">&times;</button>
        </div>
        <form method="POST" action="{{ route('profil.destroy.post') }}">
            @csrf
            <p style="font-size: 13.5px; color: var(--slate-700); margin-bottom: 14px;">
                Apakah Anda yakin ingin menghapus akun <strong>{{ $user->username }}</strong> ({{ $user->nama }})?
            </p>
            <div class="alert alert-danger" style="font-size: 12.5px; margin-bottom: 14px;">
                Untuk mencegah tindakan tidak sengaja, silakan ketik kata <strong>HAPUS</strong> di bawah ini untuk konfirmasi:
            </div>
            <div class="form-group">
                <input type="text" name="konfirmasi_teks" placeholder="Ketik HAPUS" required autocomplete="off">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-hapus-akun')">Batal</button>
                <button type="submit" class="btn-danger">Hapus Akun Permanen</button>
            </div>
        </form>
    </div>
</div>
@endsection
