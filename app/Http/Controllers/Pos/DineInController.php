<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DineInController extends Controller
{
    public function index()
    {
        $mejas = Meja::with('status_meja')->orderBy(DB::raw('CAST(REGEXP_REPLACE(nomor_meja, "[^0-9]", "") AS UNSIGNED)'), 'asc')->get();

        // Jenis Menu Dine In (misal id 1)
        $menus = Menu::with(['kategori_menu', 'resep_menu.bahan_baku'])
            ->where(function ($query) {
                $query->where('jenis_menu_id', 1)
                    ->orWhereNull('jenis_menu_id');
            })
            ->where('status_aktif', true)
            ->get();

        $kategoriIds = $menus->pluck('kategori_menu_id')->filter()->unique();
        $kategoris = KategoriMenu::whereIn('id', $kategoriIds)->orderBy('id', 'asc')->get();

        // Open Bills: Pesanan (Dine In) Menunggu Pembayaran
        // 1 = Dine In. status_pesanan_id 1 = Menunggu (Konfirmasi/Bayar)
        $openBillsRaw = Pesanan::with(['meja', 'detail_pesanan.menu'])
            ->where('jenis_pesanan_id', 1)
            ->where('status_pesanan_id', 1)
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        // Map ke format yang dipakai frontend (bill.items, bill.nama_konsumen, dll)
        $openBills = $openBillsRaw->map(function ($p) {
            $arr = $p->toArray();
            // Ekstrak nama_konsumen dari catatan atau fallback
            $namaKonsumen = null;
            if (! empty($p->catatan)) {
                // Format: "Pemesan: Nama" (QR Self-Order terbaru)
                if (preg_match('/^Pemesan:\s*(.+)$/m', $p->catatan, $m)) {
                    $namaKonsumen = trim($m[1]);
                }
                // Format: "Self-Order QR (Nama) | Order_ID_Dinein:X"
                elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $p->catatan, $m)) {
                    $namaKonsumen = trim($m[1]);
                }
                // Format POS: "Nama (N tamu)"
                elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $p->catatan, $m)) {
                    $namaKonsumen = trim($m[1]);
                }
                // Fallback: ambil baris pertama catatan saja
                else {
                    $namaKonsumen = trim(explode('|', $p->catatan)[0]);
                    $namaKonsumen = strlen($namaKonsumen) > 40 ? substr($namaKonsumen, 0, 40).'…' : $namaKonsumen;
                }
            }
            $arr['nama_konsumen'] = $namaKonsumen ?: 'Tamu';
            // Map detail_pesanan → items (format yang dipakai view)
            $arr['items'] = collect($p->detail_pesanan)->map(function ($d) {
                return [
                    'id' => $d->id,
                    'qty' => $d->jumlah,
                    'harga_satuan' => (float) $d->harga_satuan,
                    'subtotal' => (float) $d->subtotal,
                    'catatan' => $d->catatan,
                    'menu' => $d->menu ? [
                        'id' => $d->menu->id,
                        'nama' => $d->menu->nama_menu ?? $d->menu->nama ?? 'Menu',
                        'harga' => (float) ($d->menu->harga_jual ?? $d->menu->harga ?? 0),
                        'foto' => $d->menu->foto,
                    ] : null,
                ];
            })->values()->all();
            // Hitung ulang total_tagihan dari items jika 0
            if (empty($arr['total_tagihan']) || $arr['total_tagihan'] == 0) {
                $arr['total_tagihan'] = collect($arr['items'])->sum(fn ($i) => $i['subtotal']);
            }

            return $arr;
        });

        // Riwayat Transaksi: Selesai atau Dibatalkan
        // status_pesanan_id 5 = Selesai, 6 = Dibatalkan
        $riwayatTransaksiRaw = Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran.diproses_oleh_pengguna', 'kasir'])
            ->where('jenis_pesanan_id', 1)
            ->whereIn('status_pesanan_id', [5, 6])
            ->orderBy('diperbarui_pada', 'desc')
            ->take(200)
            ->get();

        $riwayatTransaksi = $riwayatTransaksiRaw->map(function ($p) {
            $arr = $p->toArray();
            $namaKonsumen = null;
            if (! empty($p->catatan)) {
                if (preg_match('/^Pemesan:\s*(.+)$/m', $p->catatan, $m)) {
                    $namaKonsumen = trim($m[1]);
                } elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $p->catatan, $m)) {
                    $namaKonsumen = trim($m[1]);
                } elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $p->catatan, $m)) {
                    $namaKonsumen = trim($m[1]);
                } else {
                    $namaKonsumen = trim(explode('|', $p->catatan)[0]);
                    $namaKonsumen = strlen($namaKonsumen) > 40 ? substr($namaKonsumen, 0, 40).'…' : $namaKonsumen;
                }
            }
            $arr['nama_konsumen'] = $namaKonsumen ?: 'Tamu';
            $arr['status'] = $p->status_pesanan_id == 5 ? 'selesai' : 'dibatalkan';
            $arr['items'] = collect($p->detail_pesanan)->map(function ($d) {
                return [
                    'id' => $d->id,
                    'qty' => $d->jumlah,
                    'harga_satuan' => (float) $d->harga_satuan,
                    'subtotal' => (float) $d->subtotal,
                    'catatan' => $d->catatan,
                    'menu' => $d->menu ? [
                        'id' => $d->menu->id,
                        'nama' => $d->menu->nama_menu ?? $d->menu->nama ?? 'Menu',
                        'harga' => (float) ($d->menu->harga_jual ?? $d->menu->harga ?? 0),
                        'foto' => $d->menu->foto,
                    ] : null,
                ];
            })->values()->all();
            if (empty($arr['total_tagihan']) || $arr['total_tagihan'] == 0) {
                $arr['total_tagihan'] = collect($arr['items'])->sum(fn ($i) => $i['subtotal']);
            }
            $pembayaran = $p->pembayaran->first();
            $arr['metode_bayar'] = $pembayaran->metode_pembayaran ?? 'Cash';
            $arr['kasir_name'] = $pembayaran && $pembayaran->diproses_oleh_pengguna ? $pembayaran->diproses_oleh_pengguna->name : ($p->kasir->name ?? 'Kasir');

            return $arr;
        });

        $cashiers = Pengguna::whereIn('peran_id', [1, 2, 3])->orderBy('nama')->get(['id', 'nama']);

        // Compute active table status based on open bills
        $activeMejaIds = $openBillsRaw->pluck('meja_id')->filter()->unique()->toArray();
        $mejas->each(function ($m) use ($activeMejaIds) {
            $m->status = in_array($m->id, $activeMejaIds) ? 'terisi' : 'kosong';
        });

        return view('pos.pesanan.index', compact('mejas', 'menus', 'kategoris', 'openBills', 'riwayatTransaksi', 'cashiers'));
    }

    public function storePosOrder(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:meja,id',
            'nama_konsumen' => 'required|string|max:255',
            'jumlah_tamu' => 'nullable|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menu,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                $subtotal += $menu->harga_jual * $item['qty'];
            }

            $pajak = $subtotal * 0.10; // 10% PB1 tax
            $totalTagihan = $subtotal + $pajak;

            $pesanan = Pesanan::create([
                'nomor_pesanan' => 'DIN-'.time().'-'.rand(100, 999), 'tanggal_pesanan' => now(),
                'jenis_pesanan_id' => 1, // Dine In
                'meja_id' => $request->meja_id,
                'pelayan_id' => Auth::id(),
                'status_pesanan_id' => 1, // Menunggu Konfirmasi/Pembayaran
                'jumlah_sebelum_potongan' => $subtotal,
                'jumlah_pajak' => $pajak,
                'total_tagihan' => $totalTagihan,
                'catatan' => $request->nama_konsumen.' ('.($request->jumlah_tamu ?? 1).' tamu)',
            ]);

            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $menu->id,
                    'jumlah' => $item['qty'],
                    'harga_satuan' => $menu->harga_jual,
                    'subtotal' => $menu->harga_jual * $item['qty'],
                    'catatan' => $item['catatan'] ?? null,
                ]);
            }

            // Update status meja menjadi terisi
            $meja = Meja::find($request->meja_id);
            $meja->update(['status_meja_id' => 2]); // 2 = Terisi

            DB::commit();

            $pesanan->load(['meja', 'detail_pesanan.menu']);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil disimpan.',
                'pesanan_id' => $pesanan->id,
                'pesanan' => $pesanan,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function clearTable($mejaId)
    {
        $meja = Meja::findOrFail($mejaId);

        // Cek pesanan dine in aktif
        $unpaidOrder = Pesanan::where('meja_id', $mejaId)
            ->where('jenis_pesanan_id', 1)
            ->where('status_pesanan_id', 1) // menunggu
            ->first();

        if ($unpaidOrder) {
            return redirect()->back()->with('error', 'Meja '.$meja->nomor_meja.' tidak dapat dikosongkan karena masih memiliki tagihan aktif yang BELUM DIBAYAR!');
        }

        $meja->update(['status_meja_id' => 1]); // 1 = Tersedia

        return redirect()->back()->with('success', 'Meja '.$meja->nomor_meja.' berhasil dikosongkan.');
    }

    public function printDapur($pesananId)
    {
        $pesanan = Pesanan::with('detail_pesanan.menu')->findOrFail($pesananId);

        return view('pos.pesanan.print-dapur', compact('pesanan'));
    }

    public function printMeja($pesananId)
    {
        $pesanan = Pesanan::with('detail_pesanan.menu', 'meja')->findOrFail($pesananId);

        return view('pos.pesanan.print-meja', compact('pesanan'));
    }

    public function printGabungan($pesananId)
    {
        $pesanan = Pesanan::with('detail_pesanan.menu', 'meja')->findOrFail($pesananId);

        return view('pos.pesanan.print-gabungan', compact('pesanan'));
    }

    public function printNota($pesananId)
    {
        $pesanan = Pesanan::with('detail_pesanan.menu', 'meja', 'pembayaran')->findOrFail($pesananId);

        return view('pos.pesanan.print-nota', compact('pesanan'));
    }

    public function printQrMeja(Request $request)
    {
        $query = Meja::orderBy(DB::raw('CAST(REGEXP_REPLACE(nomor_meja, "[^0-9]", "") AS UNSIGNED)'), 'asc');

        if ($request->has('meja_id')) {
            $query->where('id', $request->meja_id);
        } else {
            // Hanya ambil meja yang sudah memiliki QR token
            $query->whereNotNull('qr_token');
        }

        $mejas = $query->get();

        if ($mejas->isEmpty()) {
            return response()->send('<h3>Tidak ada data QR Code yang bisa diunduh. Silakan generate QR terlebih dahulu.</h3>');
        }

        return view('pos.pesanan.qr-meja', compact('mejas'));
    }

    public function updateSubStatus(Request $request, $pesananId)
    {
        $request->validate([
            'sub_status' => 'required|string|in:diproses,siap_diantar,sudah_diantar,menunggu_bayar',
        ]);

        // Asumsi sub_status ditangani oleh status_pesanan_id
        $pesanan = Pesanan::findOrFail($pesananId);
        // Mapping sub_status lama ke status_pesanan baru
        $statusMap = [
            'diproses' => 3,
            'siap_diantar' => 4,
            'sudah_diantar' => 5, // selesai
            'menunggu_bayar' => 1,
        ];

        $pesanan->update(['status_pesanan_id' => $statusMap[$request->sub_status] ?? 1]);

        return response()->json([
            'success' => true,
            'message' => 'Sub-status progress pesanan berhasil diperbarui.',
            'sub_status' => $request->sub_status,
        ]);
    }

    public function toggleStatusSajian($itemId)
    {
        // Fitur ini butuh kolom baru di detail_pesanan jika mau dipertahankan
        $item = DetailPesanan::findOrFail($itemId);
        // Dummy implementation since status_sajian is not in new schema

        return response()->json([
            'success' => true,
            'message' => 'Status sajian item berhasil diperbarui (Simulasi).',
        ]);
    }

    public function voidOrder(Request $request, $pesananId)
    {
        $request->validate([
            'alasan_void' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $pesanan = Pesanan::with('meja', 'pembayaran')->findOrFail($pesananId);

        if ($pesanan->status_pesanan_id === 6) { // 6 = Dibatalkan
            return response()->json(['success' => false, 'message' => 'Pesanan ini sudah dibatalkan/void.'], 400);
        }

        DB::transaction(function () use ($pesanan, $request) {
            $pesanan->update([
                'status_pesanan_id' => 6, // Dibatalkan
                'catatan' => $pesanan->catatan." [VOID: {$request->alasan_void}]",
            ]);

            if ($pesanan->pembayaran()->exists()) {
                $pesanan->pembayaran()->update([
                    'status_pembayaran_id' => 4, // 4 = Gagal/Batal
                ]);
            }

            if ($pesanan->meja) {
                $pesanan->meja->update(['status_meja_id' => 1]); // Tersedia
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Pesanan #'.$pesanan->nomor_pesanan.' berhasil dibatalkan (Void).',
        ]);
    }

    public function toggleMenuStatus($menuId)
    {
        $menu = Menu::findOrFail($menuId);
        $newStatus = ! $menu->status_aktif;
        $menu->update(['status_aktif' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Status menu berhasil diubah.',
            'status' => $newStatus ? 'aktif' : 'habis',
            'is_habis' => ! $newStatus,
        ]);
    }
}
