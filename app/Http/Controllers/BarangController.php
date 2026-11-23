<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class BarangController extends Controller
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
                  ->orWhere('pelapor', 'like', "%{$cari}%")
                  ->orWhere('deskripsi', 'like', "%{$cari}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $list = $query->orderBy('id', 'desc')->get();
        $daftarKategori = Kategori::orderBy('nama', 'asc')->get();
        $daftarLokasi = Lokasi::orderBy('nama', 'asc')->get();

        return view('barang.index', compact('list', 'daftarKategori', 'daftarLokasi'));
    }

    public function store(Request $request)
    {
        $currentYear = (int)date('Y');
        $maxYear = $currentYear + 1;

        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:150',
            'kategori' => 'required|string|exists:kategori,nama',
            'status' => 'required|in:Hilang,Ditemukan,Sudah Dikembalikan',
            'lokasi' => 'required|string|exists:lokasi,nama',
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($maxYear) {
                    $year = (int)substr($value, 0, 4);
                    if ($year < 2000 || $year > $maxYear) {
                        $fail("Tanggal kejadian tidak wajar (hanya antara tahun 2000 hingga {$maxYear}).");
                    }
                },
            ],
            'deskripsi' => 'nullable|string|max:1000',
            'pelapor' => 'required|string|min:3|max:100',
            'kontak' => ['required', 'regex:/^[0-9\-\+\s]{9,20}$/'],
        ], [
            'nama.required' => 'Nama barang wajib diisi.',
            'nama.min' => 'Nama barang minimal 3 karakter.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'kategori.exists' => 'Kategori yang dipilih tidak terdaftar di sistem.',
            'status.required' => 'Status barang wajib dipilih.',
            'status.in' => 'Status barang harus berupa Hilang, Ditemukan, atau Sudah Dikembalikan.',
            'lokasi.required' => 'Lokasi wajib dipilih.',
            'lokasi.exists' => 'Lokasi yang dipilih tidak terdaftar di sistem.',
            'tanggal.required' => 'Tanggal kejadian wajib diisi.',
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'pelapor.required' => 'Nama pelapor/penemu wajib diisi.',
            'pelapor.min' => 'Nama pelapor minimal 3 karakter.',
            'kontak.required' => 'Kontak pelapor wajib diisi.',
            'kontak.regex' => 'Nomor kontak tidak valid (minimal 9 digit angka/tanda hubung).',
        ]);

        $id = Barang::generateId();
        $validated['id'] = $id;

        Barang::create($validated);

        return redirect()->route('barang.index')
            ->with('pesan', 'simpan_sukses')
            ->with('sukses', "Data barang {$id} berhasil disimpan ke dalam sistem.");
    }

    public function update(Request $request, string $id)
    {
        $barang = Barang::findOrFail($id);

        $currentYear = (int)date('Y');
        $maxYear = $currentYear + 1;

        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:150',
            'kategori' => 'required|string|exists:kategori,nama',
            'status' => 'required|in:Hilang,Ditemukan,Sudah Dikembalikan',
            'lokasi' => 'required|string|exists:lokasi,nama',
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use ($maxYear) {
                    $year = (int)substr($value, 0, 4);
                    if ($year < 2000 || $year > $maxYear) {
                        $fail("Tanggal kejadian tidak wajar (hanya antara tahun 2000 hingga {$maxYear}).");
                    }
                },
            ],
            'deskripsi' => 'nullable|string|max:1000',
            'pelapor' => 'required|string|min:3|max:100',
            'kontak' => ['required', 'regex:/^[0-9\-\+\s]{9,20}$/'],
        ], [
            'nama.required' => 'Nama barang wajib diisi.',
            'nama.min' => 'Nama barang minimal 3 karakter.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'status.required' => 'Status barang wajib dipilih.',
            'lokasi.required' => 'Lokasi wajib dipilih.',
            'tanggal.required' => 'Tanggal kejadian wajib diisi.',
            'pelapor.required' => 'Nama pelapor/penemu wajib diisi.',
            'kontak.required' => 'Kontak pelapor wajib diisi.',
            'kontak.regex' => 'Nomor kontak tidak valid (minimal 9 digit angka).',
        ]);

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('pesan', 'simpan_sukses')
            ->with('sukses', "Data barang {$id} berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return redirect()->route('barang.index')
            ->with('pesan', 'hapus_sukses')
            ->with('sukses', "Data barang {$id} berhasil dihapus dari sistem.");
    }

    public function show(string $id)
    {
        $barang = Barang::findOrFail($id);
        return response()->json($barang);
    }
}
