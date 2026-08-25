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
    private function sortMejasByNumber($mejas)
    {
        return $mejas->sortBy(function ($m) {
            preg_match_all('/\d+/', $m->nomor_meja ?? '', $m);
            return (int) implode('', $m[0]);
        }, SORT_REGULAR);
    }

    public function index()
    {
        $mejas = $this->sortMejasByNumber(Meja::with('status_meja')->get());

        // Jenis Menu Dine In (misal id 1)
        $menus = Menu::with(['kategori_menu', 'resep_menu.bahan_baku.stok_harian', 'resep_menu.bahan_baku.stok_relasi'])
            ->where(function ($query) {
                $query->where('jenis_menu_id', 1)
                    ->orWhereNull('jenis_menu_id');
            })
            ->where('status_aktif', true)
            ->get();

        $kebutuhanBahanService = app(\App\Services\KebutuhanBahanService::class);
        $menuHabisIds = [];
        $sisaPorsiMenu = [];
        foreach ($menus as $menu) {
            $porsi = $kebutuhanBahanService->porsiTersedia($menu, \App\Models\StokBahan::JENIS_HARIAN);
            if ($porsi >= PHP_FLOAT_MAX) {
                $sisaPorsiMenu[$menu->id] = 999;
            } else {
                $sisaPorsiMenu[$menu->id] = max(0, (int) floor($porsi));
                if ($porsi < 1) {
                    $menuHabisIds[] = $menu->id;
                }
            }
        }

        $kategoriIds = $menus->pluck('kategori_menu_id')->filter()->unique();
        $kategoris = KategoriMenu::whereIn('id', $kategoriIds)->orderBy('id', 'asc')->get();

        // Open Bills & Riwayat (Pesanan Dine In yang belum selesai)
        // 1 = Dine In
        $openBillsRaw = Pesanan::with(['meja', 'pelanggan', 'detail_pesanan.menu', 'pembayaran.diverifikasi_oleh_pengguna', 'kasir', 'status_pesanan'])
            ->where('jenis_pesanan_id', 1)
            ->where('status_pesanan_id', '!=', 5) // Exclude Selesai
            ->orderBy('dibuat_pada', 'desc')
            ->take(300)
            ->get();

        // Map ke format yang dipakai frontend (bill.items, bill.nama_konsumen, dll)
        $openBills = $openBillsRaw->map(function ($p) {
            $arr = $p->toArray();
            $arr['nama_konsumen'] = $p->nama_konsumen;
            $arr['no_telepon'] = $p->no_telepon;
            $arr['sumber_pesanan'] = $p->sumber_pesanan;
            $arr['metode_pemesanan'] = $p->metode_pemesanan;

            // Status untuk UI
            if ($p->status_pesanan_id == 5) {
                $arr['status'] = 'Selesai';
                $arr['status_raw'] = 'selesai';
            } elseif ($p->status_pesanan_id == 6) {
                $arr['status'] = 'Dibatalkan';
                $arr['status_raw'] = 'dibatalkan';
            } else {
                $arr['status'] = $p->status_pesanan->nama_status ?? 'Aktif';
                $arr['status_raw'] = 'aktif';
            }

            // Map detail_pesanan → items
            $arr['items'] = collect($p->detail_pesanan)->map(function ($d) {
                return [
                    'id' => $d->id,
                    'qty' => $d->jumlah,
                    'harga_satuan' => (float) $d->harga_satuan,
                    'subtotal' => (float) $d->subtotal,
                    'catatan' => $d->catatan,
                    'status_item' => $d->status_item,
                    'is_tambahan' => (bool) $d->is_tambahan,
                    'batch_pesanan' => (int) ($d->batch_pesanan ?? 1),
                    'waktu_dipesan' => $d->waktu_dipesan ? \Carbon\Carbon::parse($d->waktu_dipesan)->format('H:i') : null,
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
            
            $pembayaran = collect($p->pembayaran)->first();
            $arr['metode_bayar'] = $pembayaran->metode_pembayaran ?? 'Cash';
            $arr['kasir'] = $pembayaran->diverifikasi_oleh_pengguna->nama ?? $p->kasir->nama ?? 'Sistem';
            
            $arr['is_new_order'] = \Carbon\Carbon::parse($p->dibuat_pada)->diffInMinutes(now()) < 15;

            return $arr;
        });

        $cashiers = Pengguna::whereIn('peran_id', [1, 2, 3])->orderBy('nama')->get(['id', 'nama']);

        // Compute active table status based on open bills
        $activeMejaIds = collect($openBills)->where('status_raw', 'aktif')->pluck('meja_id')->filter()->unique()->toArray();
        $mejas->each(function ($m) use ($activeMejaIds) {
            $m->status = in_array($m->id, $activeMejaIds) ? 'terisi' : 'kosong';
        });

        $pengaturanTransaksi = \App\Models\PengaturanTransaksi::first();

        return view('admin.pos.pesanan.index', compact('mejas', 'menus', 'kategoris', 'openBills', 'cashiers', 'menuHabisIds', 'sisaPorsiMenu', 'pengaturanTransaksi'));
    }

    public function tableStatusApi()
    {
        $mejas = $this->sortMejasByNumber(Meja::with('status_meja')->get());
            
        $allOpenBillsRaw = Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran.diverifikasi_oleh_pengguna', 'kasir', 'status_pesanan'])
            ->where('jenis_pesanan_id', 1)
            ->whereNotIn('status_pesanan_id', [5, 6]) // Exclude Selesai & Dibatalkan
            ->orderBy('dibuat_pada', 'desc')
            ->take(300)
            ->get();
            
        $openBills = $allOpenBillsRaw->map(function ($p) {
            $arr = $p->toArray();
            $arr['nama_konsumen'] = $p->nama_konsumen;
            $arr['no_telepon'] = $p->no_telepon;
            $arr['sumber_pesanan'] = $p->sumber_pesanan;
            $arr['metode_pemesanan'] = $p->metode_pemesanan;

            if ($p->status_pesanan_id == 5) {
                $arr['status'] = 'Selesai';
                $arr['status_raw'] = 'selesai';
            } elseif ($p->status_pesanan_id == 6) {
                $arr['status'] = 'Dibatalkan';
                $arr['status_raw'] = 'dibatalkan';
            } else {
                $arr['status'] = $p->status_pesanan->nama_status ?? 'Aktif';
                $arr['status_raw'] = 'aktif';
            }
            $arr['items'] = collect($p->detail_pesanan)->map(function ($d) {
                return [
                    'id' => $d->id,
                    'qty' => $d->jumlah,
                    'harga_satuan' => (float) $d->harga_satuan,
                    'subtotal' => (float) $d->subtotal,
                    'catatan' => $d->catatan,
                    'status_item' => $d->status_item,
                    'is_tambahan' => (bool) $d->is_tambahan,
                    'batch_pesanan' => (int) ($d->batch_pesanan ?? 1),
                    'waktu_dipesan' => $d->waktu_dipesan ? \Carbon\Carbon::parse($d->waktu_dipesan)->format('H:i') : null,
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
            
            $pembayaran = collect($p->pembayaran)->first();
            $arr['metode_bayar'] = $pembayaran->metode_pembayaran ?? 'Cash';
            $arr['kasir_name'] = $pembayaran && $pembayaran->diverifikasi_oleh_pengguna ? $pembayaran->diverifikasi_oleh_pengguna->nama : ($p->kasir->nama ?? 'Kasir');
            
            $arr['is_new_order'] = \Carbon\Carbon::parse($p->dibuat_pada)->diffInMinutes(now()) < 15;
            
            return $arr;
        });

        $activeMejaIds = $allOpenBillsRaw->pluck('meja_id')->filter()->unique();

        $mejas = $mejas->map(function ($m) use ($allOpenBillsRaw) {
            $arr = [
                'id' => $m->id,
                'nomor_meja' => $m->nomor_meja,
                'status' => $m->status_meja_id == 2 ? 'terisi' : 'kosong',
                'has_active_order' => false,
                'active_order' => null,
                'kot' => null,
            ];

            $order = $allOpenBillsRaw->firstWhere('meja_id', $m->id);
            if ($order) {
                $namaKonsumen = 'Tamu';
                if (! empty($order->catatan)) {
                    if (preg_match('/^Pemesan:\s*(.+)$/m', $order->catatan, $mm)) {
                        $namaKonsumen = trim($mm[1]);
                    } elseif (preg_match('/Self-Order QR \(([^)]+)\)/', $order->catatan, $mm)) {
                        $namaKonsumen = trim($mm[1]);
                    } elseif (preg_match('/^(.+?)\s*\(\d+\s*tamu\)/', $order->catatan, $mm)) {
                        $namaKonsumen = trim($mm[1]);
                    } else {
                        $namaKonsumen = trim(explode('|', $order->catatan)[0]);
                        $namaKonsumen = strlen($namaKonsumen) > 40 ? substr($namaKonsumen, 0, 40).'…' : $namaKonsumen;
                    }
                }

                $arr['has_active_order'] = true;
                $arr['active_order'] = [
                    'id' => $order->id,
                    'meja_id' => $order->meja_id,
                    'id_pesanan' => $order->id_pesanan,
                    'nama_konsumen' => $namaKonsumen,
                    'meja' => $order->meja ? $order->meja->toArray() : null,
                    'dibuat_pada' => $order->dibuat_pada ? $order->dibuat_pada->toDateTimeString() : null,
                    'total_tagihan' => (float) ($order->total_tagihan ?: collect($order->detail_pesanan)->sum(fn ($d) => $d->subtotal)),
                    'items' => $order->detail_pesanan->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'qty' => $d->jumlah,
                            'catatan' => $d->catatan,
                            'status_item' => $d->status_item,
                            'is_tambahan' => (bool) $d->is_tambahan,
                            'batch_pesanan' => (int) ($d->batch_pesanan ?? 1),
                            'menu' => $d->menu ? [
                                'id' => $d->menu->id,
                                'nama' => $d->menu->nama_menu ?? $d->menu->nama ?? 'Menu',
                            ] : null,
                        ];
                    })->values()->all(),
                ];
            }

            return $arr;
        });

        return response()->json([
            'success' => true,
            'mejas' => $mejas->values()->all(),
            'open_bills' => $openBills,
        ]);
    }

    public function storePosOrder(Request $request)
    {
        $request->validate([
            'meja_id' => 'required|exists:meja,id',
            'nama_konsumen' => 'nullable|string|max:255',
            'no_telepon' => 'nullable|string|max:30',
            'jumlah_tamu' => 'nullable|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menu,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $kebutuhanBahanService = app(\App\Services\KebutuhanBahanService::class);
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                if (! $menu) {
                    throw new \Exception("Menu tidak ditemukan (ID {$item['menu_id']}).");
                }

                if (! $kebutuhanBahanService->bahanCukup($menu, (float) $item['qty'])) {
                    throw new \Exception("Stok bahan baku untuk menu '{$menu->nama_menu}' tidak mencukupi.");
                }

                $subtotal += $menu->harga_jual * $item['qty'];
            }

            $kalkulasiService = app(\App\Services\KalkulasiPesananService::class);
            $kalkulasi = $kalkulasiService->hitungTotal($subtotal);

            // Clean customer name & phone
            $rawNama = trim($request->nama_konsumen ?? 'Tamu');
            $noTelepon = trim($request->no_telepon ?? '');
            if (empty($noTelepon) && preg_match('/[–\-\|]\s*([0-9\+]{8,15})/', $rawNama, $pm)) {
                $noTelepon = trim($pm[1]);
                $rawNama = trim(preg_replace('/[–\-\|]\s*[0-9\+]{8,15}.*$/', '', $rawNama));
            }

            $catatanString = 'Pemesan: ' . $rawNama;
            if (!empty($noTelepon)) {
                $catatanString .= ' | ' . $noTelepon;
            }

            // 1. Cek apakah meja ini sudah memiliki sesi pesanan aktif (belum selesai/batal dan belum lunas)
            $activePesanan = Pesanan::where('meja_id', $request->meja_id)
                ->where('jenis_pesanan_id', 1) // Dine In
                ->whereNotIn('status_pesanan_id', [5, 6]) // 5 = Selesai, 6 = Dibatalkan
                ->where(function ($q) {
                    $q->whereNull('status_pembayaran_id')
                        ->orWhereNotIn('status_pembayaran_id', [2, 5]); // 2 = Lunas, 5 = Selesai
                })
                ->latest('id')
                ->first();

            $isTambahan = false;

            if ($activePesanan) {
                // Meja sudah terisi -> Masukkan sebagai Pesanan Tambahan ke pesanan aktif yang sama
                $isTambahan = true;
                $maxBatch = (int) (DetailPesanan::where('pesanan_id', $activePesanan->id)->max('batch_pesanan') ?? 1);
                $newBatch = $maxBatch + 1;

                foreach ($request->items as $item) {
                    $menu = Menu::find($item['menu_id']);
                    DetailPesanan::create([
                        'pesanan_id' => $activePesanan->id,
                        'menu_id' => $menu->id,
                        'jumlah' => $item['qty'],
                        'harga_satuan' => $menu->harga_jual,
                        'subtotal' => $menu->harga_jual * $item['qty'],
                        'catatan' => $item['catatan'] ?? null,
                        'is_tambahan' => true,
                        'batch_pesanan' => $newBatch,
                        'waktu_dipesan' => now(),
                    ]);
                }

                // Hitung ulang akumulasi subtotal dari seluruh detail pesanan (awal + tambahan)
                $totalSubtotal = (float) DetailPesanan::where('pesanan_id', $activePesanan->id)->sum('subtotal');
                $kalkulasiTotal = $kalkulasiService->hitungTotal($totalSubtotal);

                $updateFields = [
                    'jumlah_sebelum_potongan' => $kalkulasiTotal['subtotal'],
                    'persentase_pajak' => $kalkulasiTotal['persentase_pajak'],
                    'jumlah_pajak' => $kalkulasiTotal['nominal_pajak'],
                    'persentase_biaya_layanan' => $kalkulasiTotal['persentase_biaya_layanan'],
                    'biaya_pelayanan' => $kalkulasiTotal['nominal_biaya_layanan'],
                    'total_tagihan' => $kalkulasiTotal['total_tagihan'],
                ];

                // Jika pesanan sebelumnya masih status Menunggu Konfirmasi (misal dari QR), otomatis konfirmasi karena diinput oleh Kasir
                if ($activePesanan->status_pesanan_id == 1) {
                    $updateFields['status_pesanan_id'] = 2; // Dikonfirmasi
                }

                $activePesanan->update($updateFields);

                // Potong stok untuk item-item baru yang belum dipotong
                $orderService = app(\App\Services\OrderService::class);
                $activePesanan->load('detail_pesanan.menu');
                $orderService->potongStokPesanan($activePesanan);

                $pesanan = $activePesanan;
            } else {
                // Belum ada pesanan aktif -> Buat Pesanan Baru
                // Input via Kasir langsung berstatus DIKONFIRMASI (status 2), tidak perlu konfirmasi lagi!
                $kodePesanan = \App\Helpers\IdCodeGenerator::generatePesananId();
                $pesananData = [
                    'id_pesanan' => $kodePesanan,
                    'tanggal_pesanan' => now(),
                    'jenis_pesanan_id' => 1, // Dine In
                    'meja_id' => $request->meja_id,
                    'pelayan_id' => \Illuminate\Support\Facades\Auth::id(),
                    'status_pesanan_id' => 2, // Dikonfirmasi langsung (Input via Kasir)
                    'status_pembayaran_id' => 1, // Belum Bayar
                    'jumlah_sebelum_potongan' => $kalkulasi['subtotal'],
                    'persentase_pajak' => $kalkulasi['persentase_pajak'],
                    'jumlah_pajak' => $kalkulasi['nominal_pajak'],
                    'persentase_biaya_layanan' => $kalkulasi['persentase_biaya_layanan'],
                    'biaya_pelayanan' => $kalkulasi['nominal_biaya_layanan'],
                    'total_tagihan' => $kalkulasi['total_tagihan'],
                    'catatan' => $catatanString,
                ];

                $pesanan = Pesanan::create($pesananData);

                foreach ($request->items as $item) {
                    $menu = Menu::find($item['menu_id']);
                    DetailPesanan::create([
                        'pesanan_id' => $pesanan->id,
                        'menu_id' => $menu->id,
                        'jumlah' => $item['qty'],
                        'harga_satuan' => $menu->harga_jual,
                        'subtotal' => $menu->harga_jual * $item['qty'],
                        'catatan' => $item['catatan'] ?? null,
                        'is_tambahan' => false,
                        'batch_pesanan' => 1,
                        'waktu_dipesan' => now(),
                    ]);
                }

                // Deduct stok bahan baku
                $orderService = app(\App\Services\OrderService::class);
                $pesanan->load('detail_pesanan.menu');
                $orderService->potongStokPesanan($pesanan);
            }

            // Update status meja menjadi terisi (status_meja_id = 2)
            $meja = Meja::find($request->meja_id);
            if ($meja) {
                $meja->update(['status_meja_id' => 2]); // 2 = Terisi
            }

            DB::commit();

            $pesanan->load(['meja', 'pelanggan', 'detail_pesanan.menu', 'pembayaran.diverifikasi_oleh_pengguna', 'kasir', 'status_pesanan']);

            $pesananArr = $pesanan->toArray();
            $pesananArr['nama_konsumen'] = $pesanan->nama_konsumen ?? $rawNama;
            $pesananArr['no_telepon'] = !empty($pesanan->no_telepon) ? $pesanan->no_telepon : (!empty($noTelepon) ? $noTelepon : '-');
            $pesananArr['sumber_pesanan'] = $pesanan->sumber_pesanan ?? 'pos';
            $pesananArr['metode_pemesanan'] = $pesanan->metode_pemesanan ?? 'Pemesanan via Kasir';
            $pesananArr['status'] = $pesanan->status_pesanan->nama_status ?? ($pesanan->status_pesanan_id == 2 ? 'Dikonfirmasi' : 'Aktif');
            $pesananArr['status_raw'] = 'aktif';
            $pesananArr['is_new'] = true;
            $pesananArr['is_new_order'] = true;
            $pesananArr['is_tambahan'] = $isTambahan;

            // Map detail_pesanan ke format items frontend
            $pesananArr['items'] = collect($pesanan->detail_pesanan)->map(function ($d) {
                return [
                    'id' => $d->id,
                    'qty' => $d->jumlah,
                    'harga_satuan' => (float) $d->harga_satuan,
                    'subtotal' => (float) $d->subtotal,
                    'catatan' => $d->catatan,
                    'status_item' => $d->status_item,
                    'is_tambahan' => (bool) $d->is_tambahan,
                    'batch_pesanan' => (int) ($d->batch_pesanan ?? 1),
                    'waktu_dipesan' => $d->waktu_dipesan ? \Carbon\Carbon::parse($d->waktu_dipesan)->format('H:i') : null,
                    'menu' => $d->menu ? [
                        'id' => $d->menu->id,
                        'nama' => $d->menu->nama_menu ?? $d->menu->nama ?? 'Menu',
                        'harga' => (float) ($d->menu->harga_jual ?? $d->menu->harga ?? 0),
                        'foto' => $d->menu->foto,
                    ] : null,
                ];
            })->values()->all();

            return response()->json([
                'success' => true,
                'message' => $isTambahan ? 'Pesanan tambahan berhasil ditambahkan ke sesi meja.' : 'Pesanan berhasil disimpan & dikonfirmasi.',
                'pesanan_id' => $pesanan->id,
                'pesanan' => $pesananArr,
                'is_tambahan' => $isTambahan,
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

    public function printBukti($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pelanggan', 'kasir', 'pelayan', 'pembayaran'])->findOrFail($pesananId);
        $pengaturan = \App\Models\PengaturanTransaksi::first();

        return view('admin.menu.qr.bukti-pesanan', compact('pesanan', 'pengaturan'));
    }

    public function printDapur($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pelanggan', 'kasir', 'pelayan'])->findOrFail($pesananId);

        return view('admin.pos.pesanan.print-dapur', compact('pesanan'));
    }

    public function printMeja($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pelanggan', 'kasir', 'pelayan'])->findOrFail($pesananId);

        return view('admin.pos.pesanan.print-meja', compact('pesanan'));
    }

    public function printGabungan($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pelanggan', 'kasir', 'pelayan'])->findOrFail($pesananId);

        return view('admin.pos.pesanan.print-gabungan', compact('pesanan'));
    }

    public function printNota($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pembayaran', 'pelanggan', 'kasir', 'pelayan'])->findOrFail($pesananId);

        return view('admin.pos.pesanan.print-nota', compact('pesanan'));
    }

    public function printQrMeja(Request $request)
    {
        $query = Meja::query();

        if ($request->has('meja_id')) {
            $query->where('id', $request->meja_id);
        } else {
            // Hanya ambil meja yang sudah memiliki QR token
            $query->whereNotNull('qr_token');
        }

        $mejas = $this->sortMejasByNumber($query->get());

        if ($mejas->isEmpty()) {
            return response()->send('<h3>Tidak ada data QR Code yang bisa diunduh. Silakan generate QR terlebih dahulu.</h3>');
        }

        return view('admin.pos.pesanan.qr-meja', compact('mejas'));
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

    public function konfirmasi($pesananId)
    {
        $pesanan = Pesanan::findOrFail($pesananId);
        
        if (in_array($pesanan->status_pesanan_id, [1, 2])) {
            $pesanan->update(['status_pesanan_id' => 3]); // 3 = Diproses
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dikonfirmasi dan langsung diproses dapur.',
            'pesanan' => $pesanan
        ]);
    }

    public function hidangkan($pesananId)
    {
        $pesanan = Pesanan::findOrFail($pesananId);
        
        $pesanan->update(['status_pesanan_id' => 8]); // 8 = Pesanan Telah Dihidangkan

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui: Pesanan Telah Dihidangkan.',
            'pesanan' => $pesanan
        ]);
    }

    public function toggleStatusSajian($itemId)
    {
        $item = DetailPesanan::findOrFail($itemId);

        $item->status_item = $item->status_item === 'disajikan' ? null : 'disajikan';
        $item->save();

        return response()->json([
            'success' => true,
            'disajikan' => $item->status_item === 'disajikan',
            'message' => $item->status_item === 'disajikan' ? 'Item ditandai sudah disajikan.' : 'Item ditandai belum disajikan.',
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
            'message' => 'Pesanan #'.$pesanan->id_pesanan.' berhasil dibatalkan (Void).',
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
