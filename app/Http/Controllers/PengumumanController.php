<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        $list = Pengumuman::orderBy('id', 'desc')->get();
        return view('pengumuman.index', compact('list'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|min:5|max:200',
            'tgl_buat' => 'required|date',
            'tgl_publikasi' => 'required|date',
            'status' => 'required|in:Draft,Terbit',
            'penulis' => 'required|string|max:100',
            'isi' => 'nullable|string',
        ]);

        $id = Pengumuman::generateId();
        $validated['id'] = $id;

        Pengumuman::create($validated);

        return redirect()->route('pengumuman.index')->with('sukses', "Pengumuman {$id} berhasil dibuat.");
    }

    public function update(Request $request, string $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $validated = $request->validate([
            'judul' => 'required|string|min:5|max:200',
            'tgl_buat' => 'required|date',
            'tgl_publikasi' => 'required|date',
            'status' => 'required|in:Draft,Terbit',
            'penulis' => 'required|string|max:100',
            'isi' => 'nullable|string',
        ]);

        $pengumuman->update($validated);

        return redirect()->route('pengumuman.index')->with('sukses', "Pengumuman {$id} berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')->with('sukses', "Pengumuman {$id} berhasil dihapus.");
    }
}
