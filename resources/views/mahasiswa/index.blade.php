<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Portal Mahasiswa - TitikTemu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="mahasiswa-container">
        <header class="mhs-navbar">
            <div class="mhs-logo">
                <h2>TitikTemu</h2>
                <span>Portal Layanan Mahasiswa</span>
            </div>
            <div class="mhs-user-area">
                <span class="mhs-greeting">Halo, <strong>{{ $currentUser->nama }}</strong> ({{ $currentUser->role }})</span>
                <a href="{{ route('profil.index') }}" class="btn-mhs-profile">Profil Saya</a>
                <button class="btn-mhs-logout" onclick="konfirmasiLogout()">Logout</button>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </header>

        <main class="mhs-content">
            @if(session('sukses'))
                <div class="alert alert-success">
                    {{ session('sukses') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Hero Banner (UB Dark Blue) -->
            <div class="mhs-hero">
                <h3>Pusat Informasi Barang Hilang & Temuan Kampus</h3>
                <p>Selamat datang, {{ $currentUser->nama }}. Cari barang Anda yang tercecer atau laporkan barang temuan Anda agar dapat diamankan oleh petugas.</p>
            </div>

            <!-- Pengumuman Terbit -->
            @if ($pengumumanTerbit->isNotEmpty())
                <div style="margin-bottom: 24px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--slate-800); margin-bottom: 12px;">Pengumuman Terbaru:</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                        @foreach ($pengumumanTerbit as $pgm)
                            <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 14px 18px; box-shadow: var(--shadow-sm);">
                                <span style="font-size: 11px; font-weight: 600; color: #0284c7; text-transform: uppercase;">{{ $pgm->tgl_publikasi }} &bull; {{ $pgm->penulis }}</span>
                                <h5 style="font-size: 14.5px; font-weight: 700; color: var(--slate-900); margin: 6px 0;">{{ $pgm->judul }}</h5>
                                <p style="font-size: 13px; color: var(--slate-600); line-height: 1.5;">{{ Str::limit($pgm->isi, 120) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="toolbar">
                <div class="search-box">
                    <input type="text" id="input-cari" placeholder="Cari nama barang, pelapor, lokasi..." oninput="filterTabel('input-cari', 'table-barang-body')">
                </div>
                <button class="btn-primary" onclick="bukaModalLapor()">+ Lapor Barang Hilang / Temuan</button>
            </div>

            <div class="content-card">
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Barang & ID</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Lokasi Terakhir</th>
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
                                    <td><span class="badge {{ $badgeClass }}">{{ $item->status }}</span></td>
                                    <td>{{ $item->lokasi }}</td>
                                    <td>{{ $item->tanggal }}</td>
                                    <td class="col-aksi">
                                        <button type="button" class="btn-action btn-action-detail" title="Lihat Detail" aria-label="Lihat Detail" onclick="tampilDetail('{{ $item->id }}', '{{ addslashes($item->nama) }}', '{{ addslashes($item->kategori) }}', '{{ addslashes($item->status) }}', '{{ addslashes($item->lokasi) }}', '{{ $item->tanggal }}', '{{ addslashes($item->deskripsi ?? '') }}', '{{ addslashes($item->pelapor) }}', '{{ addslashes($item->kontak) }}')">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 25px; color: var(--slate-500);">
                                        Data barang tidak ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL FORM LAPOR BARANG -->
    <div class="modal hidden" id="modal-lapor">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Form Pelaporan Barang (Mahasiswa)</h3>
                <button type="button" class="btn-close" onclick="tutupModal('modal-lapor')">&times;</button>
            </div>
            <form method="POST" action="{{ route('mahasiswa.store') }}">
                @csrf
                <div class="form-group">
                    <label for="mhs-nama">Nama Barang *</label>
                    <input type="text" id="mhs-nama" name="nama" required placeholder="Contoh: Flashdisk Sandisk 64GB">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="mhs-kategori">Kategori Barang *</label>
                        <select id="mhs-kategori" name="kategori" required>
                            @foreach ($daftarKategori as $kat)
                                <option value="{{ $kat->nama }}">{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mhs-status">Jenis Laporan *</label>
                        <select id="mhs-status" name="status" required>
                            <option value="Hilang">Saya Kehilangan Barang</option>
                            <option value="Ditemukan">Saya Menemukan Barang</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="mhs-lokasi">Lokasi Kejadian / Ditemukan *</label>
                        <select id="mhs-lokasi" name="lokasi" required>
                            @foreach ($daftarLokasi as $lok)
                                <option value="{{ $lok->nama }}">{{ $lok->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mhs-tanggal">Tanggal Kejadian *</label>
                        <input type="date" id="mhs-tanggal" name="tanggal" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="mhs-deskripsi">Deskripsi Ciri Khusus</label>
                    <textarea id="mhs-deskripsi" name="deskripsi" placeholder="Warna, nomor seri, gantungan, merk, dsb..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="tutupModal('modal-lapor')">Batal</button>
                    <button type="submit" class="btn-primary">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DETAIL BARANG -->
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
                    <div class="profile-label">Pelapor / Sumber</div>
                    <div class="profile-value" id="det-pelapor">-</div>
                </div>
                <div class="profile-row" style="border-bottom: none;">
                    <div class="profile-label">Kontak Pengamanan</div>
                    <div class="profile-value" id="det-kontak" style="font-weight: 600;">-</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="tutupModal('modal-detail')">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        // Mencegah cache tombol Back setelah logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });

        function konfirmasiLogout() {
            if (confirm('Apakah Anda yakin ingin keluar dari akun?')) {
                document.getElementById('logout-form').submit();
            }
        }
        function bukaModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function tutupModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        function bukaModalLapor() {
            bukaModal('modal-lapor');
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
        function filterTabel(inputId, bodyId) {
            var filter = document.getElementById(inputId).value.toLowerCase();
            var rows = document.getElementById(bodyId).getElementsByTagName('tr');
            for (var i = 0; i < rows.length; i++) {
                var text = rows[i].textContent || rows[i].innerText;
                rows[i].style.display = text.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
            }
        }
    </script>
</body>
</html>
