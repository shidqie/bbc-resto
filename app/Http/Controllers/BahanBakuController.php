<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahan;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\MutasiStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        $query = BahanBaku::with(['kategoriBahan', 'satuan', 'supplier']);

        // Stats
        $totalBahan = BahanBaku::count();
        $stokAman = BahanBaku::whereRaw('stok > stok_minimum')->count();
        $stokMenipis = BahanBaku::whereRaw('stok > 0 AND stok <= stok_minimum')->count();
        $stokHabis = BahanBaku::where('stok', '<=', 0)->count();

        $statsPenggunaan = [
            'total' => $totalBahan,
            'resto_nasibox' => BahanBaku::whereIn('jenis_penggunaan', ['resto_nasibox', 'dine_in', 'nasi_box', 'semua'])->count(),
            'catering' => BahanBaku::where('jenis_penggunaan', 'catering')->count(),
        ];

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_bahan', 'like', "%{$search}%")
                  ->orWhere('nama_bahan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_bahan_id', $request->kategori);
        }

        if ($request->filled('jenis_penggunaan')) {
            $jenis = $request->jenis_penggunaan;
            if ($jenis === 'catering') {
                $query->where('jenis_penggunaan', 'catering');
            } elseif ($jenis === 'resto_nasibox') {
                $query->whereIn('jenis_penggunaan', ['resto_nasibox', 'dine_in', 'nasi_box', 'semua']);
            }
        }

        if ($request->filled('status_stok')) {
            switch ($request->status_stok) {
                case 'aman':
                    $query->whereRaw('stok > stok_minimum');
                    break;
                case 'menipis':
                    $query->whereRaw('stok > 0 AND stok <= stok_minimum');
                    break;
                case 'habis':
                    $query->where('stok', '<=', 0);
                    break;
            }
        }
        
        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }

        $bahanBakus = $query->latest()->paginate(12)->withQueryString();
        $kategoris = KategoriBahan::all();
        $suppliers = Supplier::all();

        return view('bahan-baku.index', compact(
            'bahanBakus', 'kategoris', 'suppliers',
            'totalBahan', 'stokAman', 'stokMenipis', 'stokHabis', 'statsPenggunaan'
        ));
    }

    public function create()
    {
        $kategoris = KategoriBahan::all();
        $satuans = Satuan::all();
        $suppliers = Supplier::all();
        
        $lastBahan = BahanBaku::latest('id')->first();
        $nextId = $lastBahan ? $lastBahan->id + 1 : 1;
        $kodeBahan = 'BB-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('bahan-baku.create', compact('kategoris', 'satuans', 'suppliers', 'kodeBahan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_bahan' => 'required|unique:bahan_bakus',
            'nama_bahan' => 'required|string|max:255',
            'jenis_penggunaan' => 'required|in:resto_nasibox,catering,dine_in,nasi_box,semua',
            'kategori_bahan_id' => 'required|exists:kategori_bahans,id',
            'satuan_id' => 'required|exists:satuans,id',
            'stok' => 'required|numeric|min:0',
            'stok_minimum' => 'required|numeric|min:0',
            'harga_terakhir' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'lokasi_penyimpanan' => 'nullable|string|max:255',
            'tanggal_kedaluwarsa' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'status' => 'boolean',
        ], [
            'nama_bahan.required' => 'Nama bahan wajib diisi.',
            'jenis_penggunaan.required' => 'Peruntukan penggunaan wajib dipilih.',
            'kategori_bahan_id.required' => 'Kategori wajib dipilih.',
            'satuan_id.required' => 'Satuan wajib dipilih.',
            'stok_minimum.min' => 'Batas minimum tidak boleh negatif.',
            'stok.min' => 'Stok awal tidak boleh negatif.',
            'harga_terakhir.min' => 'Harga beli tidak boleh negatif.',
            'kode_bahan.unique' => 'Kode bahan sudah digunakan.',
        ]);

        DB::beginTransaction();
        try {
            $bahanBaku = BahanBaku::create($validated);

            if ($bahanBaku->stok > 0) {
                MutasiStok::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'user_id' => Auth::id(),
                    'jenis_mutasi' => 'masuk',
                    'jumlah' => $bahanBaku->stok,
                    'sisa_stok' => $bahanBaku->stok,
                    'keterangan' => 'Stok Awal Bahan Baku',
                ]);
            }

            DB::commit();
            return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku ' . $bahanBaku->nama_bahan . ' berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show(BahanBaku $bahanBaku)
    {
        $bahanBaku->load(['kategoriBahan', 'satuan', 'supplier']);
        $mutasiStoks = $bahanBaku->mutasiStoks()->with('user')->latest()->take(5)->get();
        return view('bahan-baku.show', compact('bahanBaku', 'mutasiStoks'));
    }

    public function edit(BahanBaku $bahanBaku)
    {
        $kategoris = KategoriBahan::all();
        $satuans = Satuan::all();
        $suppliers = Supplier::all();
        return view('bahan-baku.edit', compact('bahanBaku', 'kategoris', 'satuans', 'suppliers'));
    }

    public function update(Request $request, BahanBaku $bahanBaku)
    {
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'jenis_penggunaan' => 'required|in:resto_nasibox,catering,dine_in,nasi_box,semua',
            'kategori_bahan_id' => 'required|exists:kategori_bahans,id',
            'satuan_id' => 'required|exists:satuans,id',
            'stok_minimum' => 'required|numeric|min:0',
            'harga_terakhir' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'lokasi_penyimpanan' => 'nullable|string|max:255',
            'tanggal_kedaluwarsa' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $bahanBaku->update($validated);

        return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku ' . $bahanBaku->nama_bahan . ' berhasil diperbarui.');
    }

    public function destroy(BahanBaku $bahanBaku)
    {
        $bahanBaku->delete();
        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil dihapus.');
    }
}
