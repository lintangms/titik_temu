@extends('layouts.app')

@section('title', 'Serah Terima')

@section('content')
<div class="content-card">
    <div class="toolbar">
        <div class="search-box">
            <input type="text" id="input-cari-st" placeholder="Cari serah terima, penerima, nama barang..." oninput="filterTabel('input-cari-st', 'table-st-body')">
        </div>
        <button class="btn-primary" onclick="bukaModalTambahST()">+ Buat Berita Acara</button>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>ID Serah Terima</th>
                    <th>Barang Terkait</th>
                    <th>Penerima</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Status</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-st-body">
                @forelse ($list as $idx => $item)
                    @php
                        $badgeClass = $item->status === 'Selesai' ? 'badge-success' : 'badge-warning';
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong style="font-family: monospace;">{{ $item->id }}</strong></td>
                        <td><strong>{{ $item->barang_nama }}</strong></td>
                        <td>{{ $item->penerima }}</td>
                        <td>{{ $item->tanggal }}</td>
                        <td>{{ $item->petugas }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $item->status }}</span></td>
                        <td class="col-aksi">
                            <div class="action-row">
                                <button type="button" class="btn-action btn-action-edit" title="Ubah Data" aria-label="Ubah Data" onclick="bukaModalEditST('{{ $item->id }}', '{{ $item->barang_id }}', '{{ addslashes($item->penerima) }}', '{{ $item->tanggal }}', '{{ addslashes($item->petugas) }}', '{{ $item->status }}', '{{ addslashes($item->catatan ?? '') }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                </button>
                                <button type="button" class="btn-action btn-action-delete" title="Hapus Data" aria-label="Hapus Data" onclick="konfirmasiHapusST('{{ $item->id }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 25px; color: var(--slate-500);">
                            Belum ada riwayat serah terima tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Serah Terima -->
<div class="modal hidden" id="modal-st">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-st-title">Buat Berita Acara Serah Terima</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-st')">&times;</button>
        </div>
        <form method="POST" id="form-st" action="{{ route('serah-terima.store') }}">
            @csrf
            <input type="hidden" name="id" id="st-id">
            <div class="form-group">
                <label for="st-barang">Barang yang Diserahkan *</label>
                <select id="st-barang" name="barang_id" required>
                    @foreach ($daftarBarang as $brg)
                        <option value="{{ $brg->id }}">{{ $brg->nama }} ({{ $brg->id }}) - Status: {{ $brg->status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="st-penerima">Nama Penerima / Pemilik *</label>
                    <input type="text" id="st-penerima" name="penerima" required placeholder="Nama lengkap penerima">
                </div>
                <div class="form-group">
                    <label for="st-tanggal">Tanggal Penyerahan *</label>
                    <input type="date" id="st-tanggal" name="tanggal" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="st-petugas">Petugas Penyerah *</label>
                    <input type="text" id="st-petugas" name="petugas" required>
                </div>
                <div class="form-group">
                    <label for="st-status">Status Serah Terima *</label>
                    <select id="st-status" name="status" required>
                        <option value="Menunggu Penyerahan">Menunggu Penyerahan</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="st-catatan">Catatan / Bukti Kepemilikan</label>
                <textarea id="st-catatan" name="catatan" placeholder="Nomor identitas/KTM penerima, kecocokan ciri fisik barang, dsb..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-st')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Berita Acara</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Serah Terima -->
<div class="modal hidden" id="modal-hapus-st">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
            <button type="button" class="btn-close" onclick="tutupModal('modal-hapus-st')">&times;</button>
        </div>
        <p style="margin-bottom: 15px; font-size: 13.5px;">Hapus riwayat serah terima: <strong id="hapus-st-id"></strong>?</p>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="tutupModal('modal-hapus-st')">Batal</button>
            <a id="btn-hapus-st" href="#" class="btn-danger" style="text-decoration: none;">Ya, Hapus</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const baseUrlST = "{{ url('serah-terima') }}";
    function bukaModalTambahST() {
        document.getElementById('modal-st-title').textContent = 'Buat Berita Acara Serah Terima';
        document.getElementById('form-st').action = "{{ route('serah-terima.store') }}";
        document.getElementById('st-id').value = '';
        document.getElementById('st-penerima').value = '';
        document.getElementById('st-tanggal').value = new Date().toISOString().split('T')[0];
        document.getElementById('st-petugas').value = "{{ addslashes(Auth::user()->nama ?? 'Bambang Sutrisno') }}";
        document.getElementById('st-status').value = 'Selesai';
        document.getElementById('st-catatan').value = '';
        bukaModal('modal-st');
    }
    function bukaModalEditST(id, barang_id, penerima, tanggal, petugas, status, catatan) {
        document.getElementById('modal-st-title').textContent = 'Ubah Serah Terima (' + id + ')';
        document.getElementById('form-st').action = baseUrlST + '/' + encodeURIComponent(id);
        document.getElementById('st-id').value = id;
        document.getElementById('st-barang').value = barang_id;
        document.getElementById('st-penerima').value = penerima;
        document.getElementById('st-tanggal').value = tanggal;
        document.getElementById('st-petugas').value = petugas;
        document.getElementById('st-status').value = status;
        document.getElementById('st-catatan').value = catatan;
        bukaModal('modal-st');
    }
    function konfirmasiHapusST(id) {
        document.getElementById('hapus-st-id').textContent = id;
        document.getElementById('btn-hapus-st').href = baseUrlST + '/hapus/' + encodeURIComponent(id);
        bukaModal('modal-hapus-st');
    }
</script>
@endpush
