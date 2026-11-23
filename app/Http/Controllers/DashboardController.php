<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $total = Barang::count();
        $hilang = Barang::where('status', 'Hilang')->count();
        $ditemukan = Barang::where('status', 'Ditemukan')->count();
        $dikembalikan = Barang::where('status', 'Sudah Dikembalikan')->count();

        $recentItems = Barang::orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact('total', 'hilang', 'ditemukan', 'dikembalikan', 'recentItems'));
    }
}
