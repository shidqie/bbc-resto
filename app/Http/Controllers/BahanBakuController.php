<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use App\Models\Satuan;
use App\Models\MutasiStok;
use App\Models\StokBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan'])
                  ->leftJoin('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                  ->select('bahan_baku.*', 'stok_bahan_baku.jumlah_stok as stok');

        $totalBahan = BahanBaku::count();
        $stokAman = StokBahanBaku::join('bahan_baku', 'stok_bahan_baku.bahan_baku_id', '=', 'bahan_baku.id')
                    ->whereRaw('stok_bahan_baku.jumlah_stok > bahan_baku.stok_minimal')->count();
        $stokMenipis = StokBahanBaku::join('bahan_baku', 'stok_bahan_baku.bahan_baku_id', '=', 'bahan_baku.id')
                    ->whereRaw('stok_bahan_baku.jumlah_stok > 0 AND stok_bahan_baku.jumlah_stok <= bahan_baku.stok_minimal')->count();
        $stokHabis = StokBahanBaku::where('jumlah_stok', '<=', 0)->count();

        $statsPenggunaan = [
            'total' => $totalBahan,
            'resto_nasibox' => $totalBahan,
            'catering' => $totalBahan,
        ];

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('nama_bahan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_bahan_baku_id', $request->kategori);
        }

        if ($request->filled('status_stok')) {
            switch ($request->status_stok) {
                case 'aman':
                    $query->whereRaw('stok_bahan_baku.jumlah_stok > bahan_baku.stok_minimal');
                    break;
                case 'menipis':
                    $query->whereRaw('stok_bahan_baku.jumlah_stok > 0 AND stok_bahan_baku.jumlah_stok <= bahan_baku.stok_minimal');
                    break;
                case 'habis':
                    $query->where('stok_bahan_baku.jumlah_stok', '<=', 0);
                    break;
            }
        }

        $bahanBakus = $query->orderBy('bahan_baku.id', 'desc')->paginate(12)->withQueryString();
        $kategoris = KategoriBahanBaku::all();

        return view('inventory.bahan-baku.index', compact(
            'bahanBakus', 'kategoris',
            'totalBahan', 'stokAman', 'stokMenipis', 'stokHabis', 'statsPenggunaan'
        ));
    }

    public function create()
    {
        $kategoris = KategoriBahanBaku::all();
        $satuans = Satuan::all();
        $kodeBahan = 'BB-' . strtoupper(uniqid());

        return view('inventory.bahan-baku.create', compact('kategoris', 'satuans', 'kodeBahan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_bahan_baku' => 'required|unique:bahan_baku',
            'nama_bahan' => 'required|string|max:255',
            'kategori_bahan_baku_id' => 'required|exists:kategori_bahan_baku,id',
            'satuan_id' => 'required|exists:satuan,id',
            'stok' => 'nullable|numeric|min:0',
            'stok_minimal' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status_aktif' => 'boolean',
        ]);

        if (!isset($validated['stok'])) $validated['stok'] = 0;

        DB::beginTransaction();
        try {
            $bahanBaku = BahanBaku::create([
                'kode_bahan_baku' => $validated['kode_bahan_baku'],
                'nama_bahan' => $validated['nama_bahan'],
                'kategori_bahan_baku_id' => $validated['kategori_bahan_baku_id'],
                'satuan_id' => $validated['satuan_id'],
                'stok_minimal' => $validated['stok_minimal'],
                'deskripsi' => $validated['deskripsi'],
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            StokBahanBaku::create([
                'bahan_baku_id' => $bahanBaku->id,
                'jumlah_stok' => $validated['stok'],
                'terakhir_diperbarui' => now()
            ]);

            if ($validated['stok'] > 0) {
                MutasiStok::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'jenis_mutasi_stok_id' => 3, // Penyesuaian Masuk
                    'jumlah' => $validated['stok'],
                    'satuan_id' => $bahanBaku->satuan_id,
                    'keterangan' => 'Stok Awal Bahan Baku',
                    'dibuat_oleh' => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku ' . $bahanBaku->nama_bahan . ' berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $bahanBaku = BahanBaku::with(['kategori_bahan_baku', 'satuan'])->findOrFail($id);
        $stok = StokBahanBaku::where('bahan_baku_id', $id)->first();
        $bahanBaku->stok = $stok ? $stok->jumlah_stok : 0;
        
        $mutasiStoks = MutasiStok::with('dibuat_oleh_pengguna')->where('bahan_baku_id', $id)->latest()->take(5)->get();
        return view('inventory.bahan-baku.show', compact('bahanBaku', 'mutasiStoks'));
    }

    public function edit($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $kategoris = KategoriBahanBaku::all();
        $satuans = Satuan::all();
        return view('inventory.bahan-baku.edit', compact('bahanBaku', 'kategoris', 'satuans'));
    }

    public function update(Request $request, $id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'kategori_bahan_baku_id' => 'required|exists:kategori_bahan_baku,id',
            'satuan_id' => 'required|exists:satuan,id',
            'stok_minimal' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status_aktif' => 'boolean',
        ]);

        $bahanBaku->update($validated);
        return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku ' . $bahanBaku->nama_bahan . ' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $bahanBaku->delete(); // Cascades ideally
        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil dihapus.');
    }
}
