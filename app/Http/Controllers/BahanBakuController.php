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
        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan', 'stok_harian', 'stok_catering_balance']);

        $totalBahan = BahanBaku::count();
        $stokAman = StokBahan::harian()->join('bahan_baku', 'stok_bahan.bahan_baku_id', '=', 'bahan_baku.id')
            ->whereColumn('stok_bahan.jumlah_stok', '>', 'stok_bahan.stok_minimal')->count();
        $stokMenipis = StokBahan::harian()->join('bahan_baku', 'stok_bahan.bahan_baku_id', '=', 'bahan_baku.id')
            ->where('stok_bahan.jumlah_stok', '>', 0)
            ->whereColumn('stok_bahan.jumlah_stok', '<=', 'stok_bahan.stok_minimal')->count();
        $stokHabis = StokBahan::harian()->where('jumlah_stok', '<=', 0)->count();

        $statsPenggunaan = [
            'total' => $totalBahan,
            'resto_nasibox' => $totalBahan,
            'catering' => $totalBahan,
        ];

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
        $kodeBahan = 'BB-'.strtoupper(uniqid());

        return view('inventory.bahan-baku.create', compact('kategoris', 'satuans', 'kodeBahan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_bahan' => 'required|unique:bahan_baku,kode_bahan',
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
            $bahanBaku = BahanBaku::create([
                'kode_bahan' => $validated['kode_bahan'],
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

    public function edit($id)
    {
        $bahanBaku = BahanBaku::with('stok_harian', 'stok_catering_balance')->findOrFail($id);
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
