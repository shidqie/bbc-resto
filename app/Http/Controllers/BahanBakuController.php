<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use App\Models\MutasiStok;
use App\Models\Satuan;
use App\Models\StokBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_bahan', 'like', "%{$search}%")
                    ->orWhere('nama_bahan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_bahan_baku_id', $request->kategori);
        }

        $bahanBakus = $query->orderBy('bahan_baku.id', 'desc')->paginate(12)->withQueryString();
        $kategorisPage = KategoriBahanBaku::withCount('bahan_bakus')->orderBy('id', 'desc')->paginate(12)->withQueryString();
        $satuansPage = Satuan::withCount('bahan_bakus')->orderBy('id', 'desc')->paginate(12)->withQueryString();
        
        $kategoris = KategoriBahanBaku::all();
        $satuans = Satuan::all();

        $tab = $request->input('tab', 'semua');

        $totalBahan = BahanBaku::count();
        $totalKategori = KategoriBahanBaku::count();
        $totalSatuan = Satuan::count();
        $bahanAktif = BahanBaku::where('status_aktif', true)->count();

        return view('inventory.bahan-baku.index', compact(
            'bahanBakus', 'kategoris', 'satuans', 'kategorisPage', 'satuansPage', 'tab',
            'totalBahan', 'totalKategori', 'totalSatuan', 'bahanAktif'
        ));
    }

    public function create()
    {
        return redirect()->route('bahan-baku.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_bahan' => 'nullable|string|unique:bahan_baku,kode_bahan',
            'nama_bahan' => 'required|string|max:255',
            'kategori_bahan_baku_id' => 'required|exists:kategori_bahan_baku,id',
            'satuan_id' => 'required|exists:satuan,id',
            'stok' => 'nullable|numeric|min:0',
            'stok_minimal_harian' => 'required|numeric|min:0',
            'stok_minimal_catering' => 'nullable|numeric|min:0',
            'jenis_peruntukan' => 'required|in:Reguler,Catering,Semua',
            'status_aktif' => 'boolean',
        ]);

        if (! isset($validated['stok'])) {
            $validated['stok'] = 0;
        }

        DB::beginTransaction();
        try {
            $kodeBahan = $validated['kode_bahan'] ?: 'BB-'.strtoupper(uniqid());
            
            $bahanBaku = BahanBaku::create([
                'kode_bahan' => $kodeBahan,
                'nama_bahan' => $validated['nama_bahan'],
                'kategori_bahan_baku_id' => $validated['kategori_bahan_baku_id'],
                'satuan_id' => $validated['satuan_id'],
                'stok_minimal' => $validated['stok_minimal_harian'],
                'jenis_peruntukan' => $validated['jenis_peruntukan'],
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            // Saldo dibuka untuk dua jenis persediaan (Harian + Catering) tanpa menggandakan master bahan.
            StokBahan::create([
                'bahan_baku_id' => $bahanBaku->id,
                'jenis_persediaan' => StokBahan::JENIS_HARIAN,
                'jumlah_stok' => $validated['stok'],
                'stok_minimal' => $validated['stok_minimal_harian'],
                'terakhir_diperbarui' => now(),
            ]);

            StokBahan::create([
                'bahan_baku_id' => $bahanBaku->id,
                'jenis_persediaan' => StokBahan::JENIS_CATERING,
                'jumlah_stok' => 0,
                'stok_minimal' => $validated['stok_minimal_catering'] ?? $validated['stok_minimal_harian'],
                'terakhir_diperbarui' => now(),
            ]);

            if ($validated['stok'] > 0) {
                MutasiStok::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'jenis_mutasi_stok_id' => 3, // Penyesuaian Masuk
                    'jumlah' => $validated['stok'],
                    'satuan_id' => $bahanBaku->satuan_id,
                    'tanggal_mutasi' => now(),
                    'jenis_persediaan' => StokBahan::JENIS_HARIAN,
                    'keterangan' => 'Stok Awal Bahan Baku',
                    'dibuat_oleh' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku '.$bahanBaku->nama_bahan.' berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        $bahanBaku = BahanBaku::with(['kategori_bahan_baku', 'satuan', 'stok_harian', 'stok_catering_balance'])->findOrFail($id);

        $mutasiStoks = MutasiStok::with(['dibuat_oleh_pengguna', 'jenis_mutasi_stok'])->where('bahan_baku_id', $id)->latest('dibuat_pada')->take(5)->get();

        return view('inventory.bahan-baku.show', compact('bahanBaku', 'mutasiStoks'));
    }

    public function drawer($id)
    {
        $bahanBaku = BahanBaku::with(['kategori_bahan_baku', 'satuan'])->findOrFail($id);
        $mutasiStoks = MutasiStok::with(['jenis_mutasi_stok'])->where('bahan_baku_id', $id)->latest('tanggal_mutasi')->take(10)->get();

        return view('inventory.bahan-baku.drawer', compact('bahanBaku', 'mutasiStoks'));
    }

    public function edit($id)
    {
        BahanBaku::findOrFail($id);

        return redirect()->route('bahan-baku.index');
    }

    public function storeSatuanAjax(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:50',
            'singkatan' => 'nullable|string|max:20',
        ]);

        $satuan = Satuan::create([
            'nama_satuan' => $request->nama_satuan,
            'singkatan' => $request->singkatan,
        ]);

        return response()->json($satuan);
    }

    // ─── CRUD Kategori Bahan Baku ───
    public function storeKategori(Request $request)
    {
        $request->validate(['nama_kategori' => 'required|string|max:255|unique:kategori_bahan_baku,nama_kategori']);
        KategoriBahanBaku::create(['nama_kategori' => $request->nama_kategori]);
        return back()->with('success', 'Kategori bahan baku berhasil ditambahkan.');
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = KategoriBahanBaku::findOrFail($id);
        $request->validate(['nama_kategori' => 'required|string|max:255|unique:kategori_bahan_baku,nama_kategori,'.$kategori->id]);
        $kategori->update(['nama_kategori' => $request->nama_kategori]);
        return back()->with('success', 'Kategori bahan baku berhasil diperbarui.');
    }

    public function destroyKategori($id)
    {
        $kategori = KategoriBahanBaku::withCount('bahan_bakus')->findOrFail($id);
        if ($kategori->bahan_bakus_count > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh bahan baku.');
        }
        $kategori->delete();
        return back()->with('success', 'Kategori bahan baku berhasil dihapus.');
    }

    // ─── CRUD Satuan ───
    public function storeSatuan(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:50|unique:satuan,nama_satuan',
            'singkatan' => 'nullable|string|max:20'
        ]);
        Satuan::create($request->only('nama_satuan', 'singkatan'));
        return back()->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function updateSatuan(Request $request, $id)
    {
        $satuan = Satuan::findOrFail($id);
        $request->validate([
            'nama_satuan' => 'required|string|max:50|unique:satuan,nama_satuan,'.$satuan->id,
            'singkatan' => 'nullable|string|max:20'
        ]);
        $satuan->update($request->only('nama_satuan', 'singkatan'));
        return back()->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroySatuan($id)
    {
        $satuan = Satuan::withCount('bahan_bakus')->findOrFail($id);
        if ($satuan->bahan_bakus_count > 0) {
            return back()->with('error', 'Satuan tidak dapat dihapus karena masih digunakan oleh bahan baku.');
        }
        $satuan->delete();
        return back()->with('success', 'Satuan berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        $validated = $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'kategori_bahan_baku_id' => 'required|exists:kategori_bahan_baku,id',
            'satuan_id' => 'required|exists:satuan,id',
            'stok_minimal_harian' => 'required|numeric|min:0',
            'stok_minimal_catering' => 'nullable|numeric|min:0',
            'jenis_peruntukan' => 'required|in:Reguler,Catering,Semua',
            'status_aktif' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $bahanBaku->update([
                'nama_bahan' => $validated['nama_bahan'],
                'kategori_bahan_baku_id' => $validated['kategori_bahan_baku_id'],
                'satuan_id' => $validated['satuan_id'],
                'stok_minimal' => $validated['stok_minimal_harian'],
                'jenis_peruntukan' => $validated['jenis_peruntukan'],
                'status_aktif' => $validated['status_aktif'] ?? true,
            ]);

            // Perbarui batas minimum per jenis persediaan (bukan menghapus saldo).
            $stokHarian = StokBahan::firstOrCreate(
                ['bahan_baku_id' => $bahanBaku->id, 'jenis_persediaan' => StokBahan::JENIS_HARIAN],
                ['jumlah_stok' => 0, 'stok_minimal' => 0, 'terakhir_diperbarui' => now()]
            );
            $stokHarian->stok_minimal = $validated['stok_minimal_harian'];
            $stokHarian->save();

            $stokCatering = StokBahan::firstOrCreate(
                ['bahan_baku_id' => $bahanBaku->id, 'jenis_persediaan' => StokBahan::JENIS_CATERING],
                ['jumlah_stok' => 0, 'stok_minimal' => 0, 'terakhir_diperbarui' => now()]
            );
            $stokCatering->stok_minimal = $validated['stok_minimal_catering'] ?? $validated['stok_minimal_harian'];
            $stokCatering->save();

            DB::commit();

            return redirect()->route('bahan-baku.index')->with('success', 'Data bahan baku '.$bahanBaku->nama_bahan.' berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);

        // Bahan yang sudah dipakai pada resep/transaksi hanya bisa dinonaktifkan.
        $dipakai = DB::table('resep_menu')->where('bahan_baku_id', $id)->exists()
            || DB::table('mutasi_stok')->where('bahan_baku_id', $id)->exists()
            || DB::table('detail_pengadaan_bahan')->where('bahan_baku_id', $id)->exists();

        if ($dipakai) {
            $bahanBaku->update(['status_aktif' => false]);

            return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku sedang digunakan, hanya dapat dinonaktifkan.');
        }

        $bahanBaku->delete();

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil dihapus.');
    }
}
