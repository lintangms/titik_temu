@extends('layouts.app')

@section('title', 'Kategori Barang')

@section('content')
<div class="content-card">
    <div class="toolbar">
        <div class="search-box">
            <input type="text" id="input-cari-kategori" placeholder="Cari nama kategori..." oninput="filterTabel('input-cari-kategori', 'table-kategori-body')">
        </div>
        <button class="btn-primary" onclick="bukaModalTambahKategori()">+ Tambah Kategori</button>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>ID Kategori</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi / Contoh Barang</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-kategori-body">
                @forelse ($list as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong style="font-family: monospace;">{{ $item->id }}</strong></td>
                        <td><strong>{{ $item->nama }}</strong></td>
                        <td>{{ $item->deskripsi ?? '-' }}</td>
                        <td class="col-aksi">
                            <div class="action-row">
                                <button type="button" class="btn-action btn-action-edit" title="Ubah Kategori" aria-label="Ubah Kategori" onclick="bukaModalEditKategori('{{ $item->id }}', '{{ addslashes($item->nama) }}', '{{ addslashes($item->deskripsi ?? '') }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                </button>
                                <button type="button" class="btn-action btn-action-delete" title="Hapus Kategori" aria-label="Hapus Kategori" onclick="konfirmasiHapusKategori('{{ $item->id }}', '{{ addslashes($item->nama) }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 25px; color: var(--slate-500);">
                            Belum ada kategori yang ditambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Kategori -->
<div class="modal hidden" id="modal-kategori">
    <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">
            <h3 id="modal-kat-title">Tambah Kategori</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-kategori')">&times;</button>
        </div>
        <form method="POST" id="form-kategori" action="{{ route('kategori.store') }}">
            @csrf
            <input type="hidden" name="id" id="kat-id">
            <div class="form-group">
                <label for="kat-nama">Nama Kategori *</label>
                <input type="text" id="kat-nama" name="nama" required placeholder="Contoh: Dokumen">
            </div>
            <div class="form-group">
                <label for="kat-deskripsi">Deskripsi</label>
                <textarea id="kat-deskripsi" name="deskripsi" placeholder="Contoh barang dalam kategori ini..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-kategori')">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Kategori -->
<div class="modal hidden" id="modal-hapus-kategori">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-hapus-kategori')">&times;</button>
        </div>
        <p style="margin-bottom: 15px; font-size: 13.5px;">Apakah Anda yakin ingin menghapus kategori: <strong id="hapus-kat-nama"></strong>?</p>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="tutupModal('modal-hapus-kategori')">Batal</button>
            <a id="btn-hapus-kat" href="#" class="btn-danger" style="text-decoration: none;">Ya, Hapus</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const baseUrlKat = "{{ url('kategori') }}";
    function bukaModalTambahKategori() {
        document.getElementById('modal-kat-title').textContent = 'Tambah Kategori';
        document.getElementById('form-kategori').action = "{{ route('kategori.store') }}";
        document.getElementById('kat-id').value = '';
        document.getElementById('kat-nama').value = '';
        document.getElementById('kat-deskripsi').value = '';
        bukaModal('modal-kategori');
    }
    function bukaModalEditKategori(id, nama, deskripsi) {
        document.getElementById('modal-kat-title').textContent = 'Ubah Kategori (' + id + ')';
        document.getElementById('form-kategori').action = baseUrlKat + '/' + encodeURIComponent(id);
        document.getElementById('kat-id').value = id;
        document.getElementById('kat-nama').value = nama;
        document.getElementById('kat-deskripsi').value = deskripsi;
        bukaModal('modal-kategori');
    }
    function konfirmasiHapusKategori(id, nama) {
        document.getElementById('hapus-kat-nama').textContent = nama + ' (' + id + ')';
        document.getElementById('btn-hapus-kat').href = baseUrlKat + '/hapus/' + encodeURIComponent(id);
        bukaModal('modal-hapus-kategori');
    }
</script>
@endpush
