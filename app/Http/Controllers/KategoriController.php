<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $list = Kategori::orderBy('id', 'asc')->get();
        return view('kategori.index', compact('list'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|min:2|max:100',
            'deskripsi' => 'nullable|string',
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.min' => 'Nama kategori minimal 2 karakter.',
        ]);

        $id = Kategori::generateId();
        $validated['id'] = $id;

        Kategori::create($validated);

        return redirect()->route('kategori.index')->with('sukses', "Kategori {$id} berhasil ditambahkan.");
    }

    public function update(Request $request, string $id)
    {
        $kategori = Kategori::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|min:2|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        $kategori->update($validated);

        return redirect()->route('kategori.index')->with('sukses', "Kategori {$id} berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return redirect()->route('kategori.index')->with('sukses', "Kategori {$id} berhasil dihapus.");
    }
}
