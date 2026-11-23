<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index()
    {
        $list = Lokasi::orderBy('id', 'asc')->get();
        return view('lokasi.index', compact('list'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:100',
            'area' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ], [
            'nama.required' => 'Nama lokasi wajib diisi.',
            'area.required' => 'Nama area gedung wajib diisi.',
        ]);

        $id = Lokasi::generateId();
        $validated['id'] = $id;

        Lokasi::create($validated);

        return redirect()->route('lokasi.index')->with('sukses', "Lokasi {$id} berhasil ditambahkan.");
    }

    public function update(Request $request, string $id)
    {
        $lokasi = Lokasi::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:100',
            'area' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $lokasi->update($validated);

        return redirect()->route('lokasi.index')->with('sukses', "Lokasi {$id} berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->delete();

        return redirect()->route('lokasi.index')->with('sukses', "Lokasi {$id} berhasil dihapus.");
    }
}
