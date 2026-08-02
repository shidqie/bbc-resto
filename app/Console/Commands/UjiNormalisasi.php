<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UjiNormalisasi extends Command
{
    protected $signature = 'normalisasi:uji';

    protected $description = 'Uji normalisasi data: FK pelanggan, data terstruktur di catatan, integritas total, duplikasi identitas';

    public function handle(): int
    {
        $fail = 0;

        $this->info('=== UJI NORMALISASI ===');

        // 1. Pesanan berlangganan (catering/nasi box) wajib memiliki pelanggan
        $r1 = DB::table('pesanan')
            ->whereIn('jenis_pesanan_id', [2, 3])
            ->whereNull('pelanggan_id')
            ->count();
        if ($r1 > 0) {
            $fail++;
            $this->error("[FAIL] {$r1} pesanan catering/nasi-box tanpa pelanggan_id (harus menunjuk tabel pelanggan)");
        } else {
            $this->info('[PASS] Semua pesanan catering/nasi-box memiliki pelanggan_id');
        }

        // 2. Data terstruktur (Pemesan:/Dibatalkan:) tidak boleh mengisi catatan
        $r2 = DB::table('pesanan')
            ->where('catatan', 'like', 'Pemesan:%')
            ->orWhere('catatan', 'like', '%Dibatalkan:%')
            ->count();
        if ($r2 > 0) {
            $fail++;
            $this->error("[FAIL] {$r2} pesanan menyimpan data terstruktur di kolom catatan (langgar 1NF)");
        } else {
            $this->info('[PASS] catatan hanya berisi teks bebas');
        }

        // 3. Integritas total: total_tagihan = subtotal detail + biaya_pengantaran
        $rows = DB::table('pengantaran as p')
            ->join('pesanan as o', 'o.id', '=', 'p.pesanan_id')
            ->leftJoin(DB::raw('(SELECT pesanan_id, SUM(subtotal) AS s FROM detail_pesanan GROUP BY pesanan_id) d'), 'd.pesanan_id', '=', 'o.id')
            ->select('o.nomor_pesanan', 'o.total_tagihan', DB::raw('COALESCE(d.s, 0) AS subtotal'), 'p.biaya_pengantaran')
            ->get();
        $bad = 0;
        foreach ($rows as $r) {
            if (abs(((float) $r->subtotal + (float) $r->biaya_pengantaran) - (float) $r->total_tagihan) > 1) {
                $bad++;
                $this->error("[FAIL] {$r->nomor_pesanan}: subtotal+ongkir (".((float) $r->subtotal + (float) $r->biaya_pengantaran).") != total_tagihan ({$r->total_tagihan})");
            }
        }
        if ($bad > 0) {
            $fail++;
        } else {
            $this->info('[PASS] total_tagihan selalu = subtotal + biaya_pengantaran ('.count($rows).' pengantaran dicek)');
        }

        // 4. Peringatan: duplikasi identitas jadwal vs pengantaran (by design, info saja)
        $r4 = DB::table('jadwal_pesanan as j')
            ->join('pengantaran as p', 'p.pesanan_id', '=', 'j.pesanan_id')
            ->whereColumn('j.nama_penerima', 'p.nama_penerima')
            ->whereColumn('j.nomor_telepon_penerima', 'p.nomor_telepon_penerima')
            ->count();
        $this->warn("[INFO] {$r4} baris duplikasi identitas jadwal/pengantaran (wajar: kontak acara vs kontak kirim)");

        // 5. Kolom wajib detail_pesanan tidak boleh NULL
        $r5 = DB::table('detail_pesanan')->whereNull('menu_id')->orWhereNull('harga_satuan')->count();
        if ($r5 > 0) {
            $fail++;
            $this->error("[FAIL] {$r5} detail_pesanan dengan nilai wajib NULL");
        } else {
            $this->info('[PASS] Semua detail_pesanan terisi menu_id & harga_satuan');
        }

        if ($fail > 0) {
            $this->error("HASIL: {$fail} kelompok pelanggaran ditemukan.");

            return Command::FAILURE;
        }

        $this->info('HASIL: Data normal (0 pelanggaran).');

        return Command::SUCCESS;
    }
}
