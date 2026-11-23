<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SerahTerima;
use Illuminate\Http\Request;

class SerahTerimaController extends Controller
{
    public function index()
    {
        $list = SerahTerima::orderBy('id', 'desc')->get();
        $daftarBarang = Barang::orderBy('id', 'desc')->get();
        return view('serah-terima.index', compact('list', 'daftarBarang'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barang_id' => 'required|string|exists:barang,id',
            'penerima' => 'required|string|min:3|max:100',
            'tanggal' => 'required|date',
            'petugas' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            'catatan' => 'nullable|string',
        ]);

        $barang = Barang::find($validated['barang_id']);
        $barangNama = $barang ? "{$barang->nama} ({$barang->id})" : $validated['barang_id'];

        $id = SerahTerima::generateId();

        SerahTerima::create([
            'id' => $id,
            'barang_id' => $validated['barang_id'],
            'barang_nama' => $barangNama,
            'penerima' => $validated['penerima'],
            'tanggal' => $validated['tanggal'],
            'petugas' => $validated['petugas'],
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? '',
        ]);

        // Jika selesai, update status barang
        if ($validated['status'] === 'Selesai' && $barang) {
            $barang->status = 'Sudah Dikembalikan';
            $barang->save();
        }

        return redirect()->route('serah-terima.index')->with('sukses', "Berita acara serah terima {$id} berhasil dibuat.");
    }

    public function update(Request $request, string $id)
    {
        $serahTerima = SerahTerima::findOrFail($id);

        $validated = $request->validate([
            'barang_id' => 'required|string|exists:barang,id',
            'penerima' => 'required|string|min:3|max:100',
            'tanggal' => 'required|date',
            'petugas' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            'catatan' => 'nullable|string',
        ]);

        $barang = Barang::find($validated['barang_id']);
        $barangNama = $barang ? "{$barang->nama} ({$barang->id})" : $validated['barang_id'];

        $serahTerima->update([
            'barang_id' => $validated['barang_id'],
            'barang_nama' => $barangNama,
            'penerima' => $validated['penerima'],
            'tanggal' => $validated['tanggal'],
            'petugas' => $validated['petugas'],
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? '',
        ]);

        if ($validated['status'] === 'Selesai' && $barang) {
            $barang->status = 'Sudah Dikembalikan';
            $barang->save();
        }

        return redirect()->route('serah-terima.index')->with('sukses', "Data serah terima {$id} berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $serahTerima = SerahTerima::findOrFail($id);
        $serahTerima->delete();

        return redirect()->route('serah-terima.index')->with('sukses', "Data serah terima {$id} berhasil dihapus.");
    }
}
