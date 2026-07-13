<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\ResepMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResepController extends Controller
{
    public function create(Menu $menu)
    {
        $menu->load('resep.bahanBaku.satuan');
        
        // Ambil semua bahan baku yang aktif
        $bahanBakus = BahanBaku::with('satuan')->where('status', 1)->orderBy('nama_bahan')->get();
        
        return view('resep.create', compact('menu', 'bahanBakus'));
    }

    public function store(Request $request, Menu $menu)
    {
        $request->validate([
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id',
            'jumlah_kebutuhan' => 'required|array',
            'jumlah_kebutuhan.*' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // Hapus resep lama
            $menu->resep()->delete();

            // Insert resep baru
            if ($request->has('bahan_baku_id')) {
                foreach ($request->bahan_baku_id as $index => $bahanId) {
                    // Ambil data bahan baku untuk mendapatkan satuan
                    $bahanBaku = BahanBaku::with('satuan')->find($bahanId);
                    
                    ResepMenu::create([
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah_kebutuhan' => $request->jumlah_kebutuhan[$index],
                        'satuan' => $bahanBaku->satuan->nama_satuan ?? '',
                        'keterangan' => $request->keterangan[$index] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('menu.show', $menu->id)->with('success', 'Resep menu berhasil diperbarui.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan resep: ' . $e->getMessage())->withInput();
        }
    }
}
