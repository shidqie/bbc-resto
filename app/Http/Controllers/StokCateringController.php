<?php

namespace App\Http\Controllers;

use App\Models\StokCatering;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class StokCateringController extends Controller
{
    public function index(Request $request)
    {
        $query = Pesanan::with(['stok_catering.bahan_baku.satuan'])
            ->whereHas('jenis_pesanan', function($q) {
                $q->where('kode_jenis', 'CATERING');
            })
            ->orderBy('tanggal_pesanan', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nomor_pesanan', 'like', "%{$search}%");
        }

        $pesanans = $query->paginate(10)->withQueryString();

        return view('inventory.stok-catering.index', compact('pesanans'));
    }
}
