@extends('layouts.app')

@section('title', 'Kelola Data Barang')

@section('content')
<div class="content-card">
    <div class="toolbar">
        <div class="search-box">
            <input type="text" id="input-cari" placeholder="Cari nama barang, pelapor, lokasi, kategori..." oninput="filterTabel('input-cari', 'table-barang-body')">
        </div>
        <button class="btn-primary" onclick="bukaModalTambah()">+ Tambah Data Barang</button>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Barang & ID</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Lokasi</th>
                    <th>Tanggal</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-barang-body">
                @forelse ($list as $idx => $item)
                    @php
                        $badgeClass = match($item->status) {
                            'Hilang' => 'badge-danger',
                            'Ditemukan' => 'badge-info',
                            'Sudah Dikembalikan' => 'badge-success',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->nama }}</strong><br>
                            <small style="color: var(--slate-500); font-family: monospace;">ID: {{ $item->id }}</small>
                        </td>
                        <td>{{ $item->kategori }}</td>
                        <td>
                            <span class="badge {{ $badgeClass }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td>{{ $item->lokasi }}</td>
                        <td>{{ $item->tanggal }}</td>
                        <td class="col-aksi">
                            <div class="action-row">
                                <button type="button" class="btn-action btn-action-detail" title="Lihat Detail" aria-label="Lihat Detail" onclick="tampilDetail('{{ $item->id }}', '{{ addslashes($item->nama) }}', '{{ addslashes($item->kategori) }}', '{{ addslashes($item->status) }}', '{{ addslashes($item->lokasi) }}', '{{ $item->tanggal }}', '{{ addslashes($item->deskripsi ?? '') }}', '{{ addslashes($item->pelapor) }}', '{{ addslashes($item->kontak) }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                                
                                <button type="button" class="btn-action btn-action-edit" title="Ubah Data" aria-label="Ubah Data" onclick="bukaModalEdit('{{ $item->id }}', '{{ addslashes($item->nama) }}', '{{ addslashes($item->kategori) }}', '{{ addslashes($item->status) }}', '{{ addslashes($item->lokasi) }}', '{{ $item->tanggal }}', '{{ addslashes($item->deskripsi ?? '') }}', '{{ addslashes($item->pelapor) }}', '{{ addslashes($item->kontak) }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                </button>
                                
                                <button type="button" class="btn-action btn-action-delete" title="Hapus Data" aria-label="Hapus Data" onclick="konfirmasiHapusBarang('{{ $item->id }}', '{{ addslashes($item->nama) }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 28px; color: var(--slate-500);">
                            Belum ada data barang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL FORM (TAMBAH / UBAH DATA BARANG) -->
<div class="modal hidden" id="modal-form">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Tambah Data Barang</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-form')">&times;</button>
        </div>
        <form method="POST" id="form-barang" action="{{ route('barang.store') }}">
            @csrf
            <input type="hidden" name="id" id="form-id">

            <div class="form-group">
                <label for="form-nama">Nama Barang *</label>
                <input type="text" id="form-nama" name="nama" required placeholder="Contoh: Dompet Kulit Cokelat">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="form-kategori">Kategori Barang *</label>
                    <select id="form-kategori" name="kategori" required>
                        @foreach ($daftarKategori as $kat)
                            <option value="{{ $kat->nama }}">{{ $kat->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="form-status">Status Barang *</label>
                    <select id="form-status" name="status" required>
                        <option value="Hilang">Hilang</option>
                        <option value="Ditemukan">Ditemukan</option>
                        <option value="Sudah Dikembalikan">Sudah Dikembalikan</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="form-lokasi">Lokasi Kejadian / Ditemukan *</label>
                    <select id="form-lokasi" name="lokasi" required>
                        @foreach ($daftarLokasi as $lok)
                            <option value="{{ $lok->nama }}">{{ $lok->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="form-tanggal">Tanggal Kejadian / Ditemukan *</label>
                    <input type="date" id="form-tanggal" name="tanggal" required>
                </div>
            </div>

            <div class="form-group">
                <label for="form-deskripsi">Deskripsi Ciri Khusus</label>
                <textarea id="form-deskripsi" name="deskripsi" placeholder="Warna, nomor seri, stiker, isi atau ciri khas khusus barang..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="form-pelapor">Nama Pelapor / Penemu *</label>
                    <input type="text" id="form-pelapor" name="pelapor" required placeholder="Nama lengkap">
                </div>

                <div class="form-group">
                    <label for="form-kontak">Kontak Pelapor (No. HP / WA) *</label>
                    <input type="tel" id="form-kontak" name="kontak" required placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-form')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DETAIL INFORMASI BARANG -->
<div class="modal hidden" id="modal-detail">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detail Informasi Barang</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-detail')">&times;</button>
        </div>
        <div>
            <div class="profile-row">
                <div class="profile-label">ID Barang</div>
                <div class="profile-value" id="det-id" style="font-family: monospace; font-weight: 700;">-</div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Nama Barang</div>
                <div class="profile-value" id="det-nama" style="font-weight: 700; color: var(--slate-900);">-</div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Kategori</div>
                <div class="profile-value" id="det-kategori">-</div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Status</div>
                <div class="profile-value"><span id="det-status" class="badge">-</span></div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Lokasi</div>
                <div class="profile-value" id="det-lokasi">-</div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Tanggal</div>
                <div class="profile-value" id="det-tanggal">-</div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Deskripsi</div>
                <div class="profile-value" id="det-deskripsi">-</div>
            </div>
            <div class="profile-row">
                <div class="profile-label">Nama Pelapor</div>
                <div class="profile-value" id="det-pelapor">-</div>
            </div>
            <div class="profile-row" style="border-bottom: none;">
                <div class="profile-label">Kontak Pelapor</div>
                <div class="profile-value" id="det-kontak" style="font-weight: 600;">-</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="tutupModal('modal-detail')">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI HAPUS BARANG -->
<div class="modal hidden" id="modal-hapus-barang">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header">
            <h3>Konfirmasi Hapus Data</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-hapus-barang')">&times;</button>
        </div>
        <div style="margin-bottom: 20px;">
            <p style="font-size: 13.5px; color: var(--slate-700); line-height: 1.5;">
                Apakah Anda yakin ingin menghapus data barang:
            </p>
            <div style="background: var(--slate-100); border: 1px solid var(--slate-200); padding: 10px 14px; border-radius: var(--radius-md); margin: 12px 0;">
                <strong id="hapus-item-nama" style="color: var(--slate-900); font-size: 14px;">-</strong><br>
                <small id="hapus-item-id" style="color: var(--slate-500); font-family: monospace;">ID: -</small>
            </div>
            <p style="font-size: 12px; color: var(--slate-500);">
                Data yang sudah dihapus tidak dapat dipulihkan kembali.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="tutupModal('modal-hapus-barang')">Batal</button>
            <a id="btn-konfirmasi-hapus" href="#" class="btn-danger" style="text-decoration: none;">Ya, Hapus Data</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const baseUrlBarang = "{{ url('barang') }}";

    function bukaModalTambah() {
        document.getElementById('modal-title').textContent = 'Tambah Data Barang';
        document.getElementById('form-barang').action = "{{ route('barang.store') }}";
        document.getElementById('form-id').value = '';
        document.getElementById('form-nama').value = '';
        document.getElementById('form-status').value = 'Hilang';
        document.getElementById('form-tanggal').value = new Date().toISOString().split('T')[0];
        document.getElementById('form-deskripsi').value = '';
        document.getElementById('form-pelapor').value = "{{ addslashes(Auth::user()->nama ?? '') }}";
        document.getElementById('form-kontak').value = "{{ addslashes(Auth::user()->kontak ?? '') }}";
        bukaModal('modal-form');
    }

    function bukaModalEdit(id, nama, kat, status, lokasi, tanggal, deskripsi, pelapor, kontak) {
        document.getElementById('modal-title').textContent = 'Ubah Data Barang (' + id + ')';
        document.getElementById('form-barang').action = baseUrlBarang + '/' + encodeURIComponent(id);
        document.getElementById('form-id').value = id;
        document.getElementById('form-nama').value = nama;
        document.getElementById('form-kategori').value = kat;
        document.getElementById('form-status').value = status;
        document.getElementById('form-lokasi').value = lokasi;
        document.getElementById('form-tanggal').value = tanggal;
        document.getElementById('form-deskripsi').value = deskripsi;
        document.getElementById('form-pelapor').value = pelapor;
        document.getElementById('form-kontak').value = kontak;
        bukaModal('modal-form');
    }

    function tampilDetail(id, nama, kat, status, lokasi, tanggal, deskripsi, pelapor, kontak) {
        document.getElementById('det-id').textContent = id;
        document.getElementById('det-nama').textContent = nama;
        document.getElementById('det-kategori').textContent = kat;
        const st = document.getElementById('det-status');
        st.textContent = status;
        st.className = 'badge ' + (status === 'Hilang' ? 'badge-danger' : (status === 'Ditemukan' ? 'badge-info' : 'badge-success'));
        document.getElementById('det-lokasi').textContent = lokasi;
        document.getElementById('det-tanggal').textContent = tanggal;
        document.getElementById('det-deskripsi').textContent = deskripsi || '-';
        document.getElementById('det-pelapor').textContent = pelapor;
        document.getElementById('det-kontak').textContent = kontak;
        bukaModal('modal-detail');
    }

    function konfirmasiHapusBarang(id, nama) {
        document.getElementById('hapus-item-nama').textContent = nama;
        document.getElementById('hapus-item-id').textContent = 'ID: ' + id;
        document.getElementById('btn-konfirmasi-hapus').href = baseUrlBarang + '/hapus/' + encodeURIComponent(id);
        bukaModal('modal-hapus-barang');
    }
</script>
@endpush
