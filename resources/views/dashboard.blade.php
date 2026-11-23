@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-banner">
    <h3>Selamat Datang, {{ Auth::user()->nama }}!</h3>
    <p>Sistem pengelolaan pelaporan, pencarian, dan pengembalian barang hilang di lingkungan kampus.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h4>{{ $total }}</h4>
        <span>Total Laporan</span>
    </div>
    <div class="stat-card">
        <h4 style="color: #b91c1c;">{{ $hilang }}</h4>
        <span>Barang Hilang</span>
    </div>
    <div class="stat-card">
        <h4 style="color: #0369a1;">{{ $ditemukan }}</h4>
        <span>Barang Ditemukan</span>
    </div>
    <div class="stat-card">
        <h4 style="color: #15803d;">{{ $dikembalikan }}</h4>
        <span>Sudah Dikembalikan</span>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h3>Laporan Barang Terbaru</h3>
        <a href="{{ route('barang.index') }}" class="btn-secondary" style="font-size: 12.5px; padding: 6px 14px;">Lihat Semua Data &rarr;</a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Lokasi</th>
                    <th>Tanggal</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentItems as $item)
                    @php
                        $badgeClass = match($item->status) {
                            'Hilang' => 'badge-danger',
                            'Ditemukan' => 'badge-info',
                            'Sudah Dikembalikan' => 'badge-success',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $item->nama }}</strong><br>
                            <small style="color: var(--slate-500); font-family: monospace;">ID: {{ $item->id }}</small>
                        </td>
                        <td>{{ $item->kategori }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $item->status }}</span></td>
                        <td>{{ $item->lokasi }}</td>
                        <td>{{ $item->tanggal }}</td>
                        <td class="col-aksi">
                            <a href="{{ route('barang.index') }}" class="btn-action btn-action-detail" title="Kelola Data" aria-label="Kelola Data">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 25px; color: var(--slate-500);">
                            Belum ada data barang terbaru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
