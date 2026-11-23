<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('id', 'like', "%{$cari}%")
                  ->orWhere('kategori', 'like', "%{$cari}%")
                  ->orWhere('lokasi', 'like', "%{$cari}%")
                  ->orWhere('deskripsi', 'like', "%{$cari}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $list = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();
        $daftarKategori = Kategori::orderBy('nama', 'asc')->get();
        $daftarLokasi = Lokasi::orderBy('nama', 'asc')->get();
        $pengumumanTerbit = Pengumuman::where('status', 'Terbit')
            ->orderBy('tgl_publikasi', 'desc')
            ->take(3)
            ->get();

        $currentUser = Auth::user();

        return view('mahasiswa.index', compact('list', 'daftarKategori', 'daftarLokasi', 'pengumumanTerbit', 'currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:150',
            'kategori' => 'required|string|exists:kategori,nama',
            'status' => 'required|in:Hilang,Ditemukan',
            'lokasi' => 'required|string|exists:lokasi,nama',
            'tanggal' => 'required|date_format:Y-m-d',
            'deskripsi' => 'nullable|string|max:1000',
            'pelapor' => 'nullable|string|max:100',
            'kontak' => 'nullable|string|max:50',
        ], [
            'nama.required' => 'Nama barang wajib diisi.',
            'nama.min' => 'Nama barang minimal 3 karakter.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'lokasi.required' => 'Lokasi wajib dipilih.',
            'tanggal.required' => 'Tanggal kejadian wajib diisi.',
        ]);

        $id = Barang::generateId();
        $validated['id'] = $id;
        $validated['pelapor'] = $validated['pelapor'] ?: ($currentUser ? $currentUser->nama : 'Mahasiswa');
        $validated['kontak'] = $validated['kontak'] ?: ($currentUser ? $currentUser->kontak : '-');

        Barang::create($validated);

        return redirect()->route('mahasiswa.index')
            ->with('sukses', "Laporan barang '{$validated['nama']}' ({$id}) berhasil dikirim ke pos satpam.");
    }
}
