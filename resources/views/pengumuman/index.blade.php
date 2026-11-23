@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
<div class="content-card">
    <div class="toolbar">
        <div class="search-box">
            <input type="text" id="input-cari-pgm" placeholder="Cari judul pengumuman..." oninput="filterTabel('input-cari-pgm', 'table-pgm-body')">
        </div>
        <button class="btn-primary" onclick="bukaModalTambahPengumuman()">+ Buat Pengumuman</button>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Judul & ID</th>
                    <th>Tgl Publikasi</th>
                    <th>Penulis</th>
                    <th>Status</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-pgm-body">
                @forelse ($list as $idx => $item)
                    @php
                        $badgeClass = $item->status === 'Terbit' ? 'badge-success' : 'badge-secondary';
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->judul }}</strong><br>
                            <small style="color: var(--slate-500); font-family: monospace;">ID: {{ $item->id }}</small>
                        </td>
                        <td>{{ $item->tgl_publikasi }}</td>
                        <td>{{ $item->penulis }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $item->status }}</span></td>
                        <td class="col-aksi">
                            <div class="action-row">
                                <button type="button" class="btn-action btn-action-edit" title="Ubah Pengumuman" aria-label="Ubah Pengumuman" onclick="bukaModalEditPengumuman('{{ $item->id }}', '{{ addslashes($item->judul) }}', '{{ $item->tgl_buat }}', '{{ $item->tgl_publikasi }}', '{{ $item->status }}', '{{ addslashes($item->penulis) }}', '{{ addslashes($item->isi ?? '') }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                </button>
                                <button type="button" class="btn-action btn-action-delete" title="Hapus Pengumuman" aria-label="Hapus Pengumuman" onclick="konfirmasiHapusPengumuman('{{ $item->id }}', '{{ addslashes($item->judul) }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 25px; color: var(--slate-500);">
                            Belum ada pengumuman yang dibuat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Pengumuman -->
<div class="modal hidden" id="modal-pengumuman">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-pgm-title">Buat Pengumuman Baru</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-pengumuman')">&times;</button>
        </div>
        <form method="POST" id="form-pengumuman" action="{{ route('pengumuman.store') }}">
            @csrf
            <input type="hidden" name="id" id="pgm-id">
            <div class="form-group">
                <label for="pgm-judul">Judul Pengumuman *</label>
                <input type="text" id="pgm-judul" name="judul" required placeholder="Contoh: Penemuan Kunci Motor di Parkiran F">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="pgm-tgl-buat">Tanggal Dibuat *</label>
                    <input type="date" id="pgm-tgl-buat" name="tgl_buat" required>
                </div>
                <div class="form-group">
                    <label for="pgm-tgl-publikasi">Tanggal Publikasi *</label>
                    <input type="date" id="pgm-tgl-publikasi" name="tgl_publikasi" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="pgm-status">Status Publikasi *</label>
                    <select id="pgm-status" name="status" required>
                        <option value="Draft">Draft</option>
                        <option value="Terbit">Terbit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pgm-penulis">Penulis / Unit *</label>
                    <input type="text" id="pgm-penulis" name="penulis" required placeholder="Contoh: Pos Satpam Pusat">
                </div>
            </div>
            <div class="form-group">
                <label for="pgm-isi">Isi Pengumuman</label>
                <textarea id="pgm-isi" name="isi" rows="4" placeholder="Detail informasi pengumuman..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-pengumuman')">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Pengumuman -->
<div class="modal hidden" id="modal-hapus-pgm">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-hapus-pgm')">&times;</button>
        </div>
        <p style="margin-bottom: 15px; font-size: 13.5px;">Hapus pengumuman: <strong id="hapus-pgm-nama"></strong>?</p>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="tutupModal('modal-hapus-pgm')">Batal</button>
            <a id="btn-hapus-pgm" href="#" class="btn-danger" style="text-decoration: none;">Ya, Hapus</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const baseUrlPgm = "{{ url('pengumuman') }}";
    function bukaModalTambahPengumuman() {
        document.getElementById('modal-pgm-title').textContent = 'Buat Pengumuman Baru';
        document.getElementById('form-pengumuman').action = "{{ route('pengumuman.store') }}";
        document.getElementById('pgm-id').value = '';
        document.getElementById('pgm-judul').value = '';
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('pgm-tgl-buat').value = today;
        document.getElementById('pgm-tgl-publikasi').value = today;
        document.getElementById('pgm-status').value = 'Terbit';
        document.getElementById('pgm-penulis').value = "{{ addslashes(Auth::user()->nama ?? 'Satpam') }}";
        document.getElementById('pgm-isi').value = '';
        bukaModal('modal-pengumuman');
    }
    function bukaModalEditPengumuman(id, judul, tgl_buat, tgl_publikasi, status, penulis, isi) {
        document.getElementById('modal-pgm-title').textContent = 'Ubah Pengumuman (' + id + ')';
        document.getElementById('form-pengumuman').action = baseUrlPgm + '/' + encodeURIComponent(id);
        document.getElementById('pgm-id').value = id;
        document.getElementById('pgm-judul').value = judul;
        document.getElementById('pgm-tgl-buat').value = tgl_buat;
        document.getElementById('pgm-tgl-publikasi').value = tgl_publikasi;
        document.getElementById('pgm-status').value = status;
        document.getElementById('pgm-penulis').value = penulis;
        document.getElementById('pgm-isi').value = isi;
        bukaModal('modal-pengumuman');
    }
    function konfirmasiHapusPengumuman(id, judul) {
        document.getElementById('hapus-pgm-nama').textContent = judul + ' (' + id + ')';
        document.getElementById('btn-hapus-pgm').href = baseUrlPgm + '/hapus/' + encodeURIComponent(id);
        bukaModal('modal-hapus-pgm');
    }
</script>
@endpush
