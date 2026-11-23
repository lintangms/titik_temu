@extends('layouts.app')

@section('title', 'Lokasi')

@section('content')
<div class="content-card">
    <div class="toolbar">
        <div class="search-box">
            <input type="text" id="input-cari-lokasi" placeholder="Cari nama lokasi atau area..." oninput="filterTabel('input-cari-lokasi', 'table-lokasi-body')">
        </div>
        <button class="btn-primary" onclick="bukaModalTambahLokasi()">+ Tambah Lokasi</button>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>ID Lokasi</th>
                    <th>Nama Lokasi</th>
                    <th>Area Kampus</th>
                    <th>Keterangan</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-lokasi-body">
                @forelse ($list as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong style="font-family: monospace;">{{ $item->id }}</strong></td>
                        <td><strong>{{ $item->nama }}</strong></td>
                        <td>{{ $item->area }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                        <td class="col-aksi">
                            <div class="action-row">
                                <button type="button" class="btn-action btn-action-edit" title="Ubah Lokasi" aria-label="Ubah Lokasi" onclick="bukaModalEditLokasi('{{ $item->id }}', '{{ addslashes($item->nama) }}', '{{ addslashes($item->area) }}', '{{ addslashes($item->keterangan ?? '') }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                </button>
                                <button type="button" class="btn-action btn-action-delete" title="Hapus Lokasi" aria-label="Hapus Lokasi" onclick="konfirmasiHapusLokasi('{{ $item->id }}', '{{ addslashes($item->nama) }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 25px; color: var(--slate-500);">
                            Belum ada lokasi yang ditambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Lokasi -->
<div class="modal hidden" id="modal-lokasi">
    <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">
            <h3 id="modal-lok-title">Tambah Lokasi</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-lokasi')">&times;</button>
        </div>
        <form method="POST" id="form-lokasi" action="{{ route('lokasi.store') }}">
            @csrf
            <input type="hidden" name="id" id="lok-id">
            <div class="form-group">
                <label for="lok-nama">Nama Lokasi *</label>
                <input type="text" id="lok-nama" name="nama" required placeholder="Contoh: Gedung Kuliah Bersama">
            </div>
            <div class="form-group">
                <label for="lok-area">Area Gedung *</label>
                <input type="text" id="lok-area" name="area" required placeholder="Contoh: Gedung GKB Lt. 1-4">
            </div>
            <div class="form-group">
                <label for="lok-keterangan">Keterangan</label>
                <textarea id="lok-keterangan" name="keterangan" placeholder="Keterangan tambahan lokasi..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-lokasi')">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Lokasi -->
<div class="modal hidden" id="modal-hapus-lokasi">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-hapus-lokasi')">&times;</button>
        </div>
        <p style="margin-bottom: 15px; font-size: 13.5px;">Apakah Anda yakin ingin menghapus lokasi: <strong id="hapus-lok-nama"></strong>?</p>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="tutupModal('modal-hapus-lokasi')">Batal</button>
            <a id="btn-hapus-lok" href="#" class="btn-danger" style="text-decoration: none;">Ya, Hapus</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const baseUrlLok = "{{ url('lokasi') }}";
    function bukaModalTambahLokasi() {
        document.getElementById('modal-lok-title').textContent = 'Tambah Lokasi';
        document.getElementById('form-lokasi').action = "{{ route('lokasi.store') }}";
        document.getElementById('lok-id').value = '';
        document.getElementById('lok-nama').value = '';
        document.getElementById('lok-area').value = '';
        document.getElementById('lok-keterangan').value = '';
        bukaModal('modal-lokasi');
    }
    function bukaModalEditLokasi(id, nama, area, keterangan) {
        document.getElementById('modal-lok-title').textContent = 'Ubah Lokasi (' + id + ')';
        document.getElementById('form-lokasi').action = baseUrlLok + '/' + encodeURIComponent(id);
        document.getElementById('lok-id').value = id;
        document.getElementById('lok-nama').value = nama;
        document.getElementById('lok-area').value = area;
        document.getElementById('lok-keterangan').value = keterangan;
        bukaModal('modal-lokasi');
    }
    function konfirmasiHapusLokasi(id, nama) {
        document.getElementById('hapus-lok-nama').textContent = nama + ' (' + id + ')';
        document.getElementById('btn-hapus-lok').href = baseUrlLok + '/hapus/' + encodeURIComponent(id);
        bukaModal('modal-hapus-lokasi');
    }
</script>
@endpush
