<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\ItemPaket;
use App\Models\Menu;
use App\Models\PilihanItemPaket;
use App\Models\ResepMenu;
use App\Models\Satuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResepDokumenSeeder extends Seeder
{
    private array $menuCache = [];
    private array $bahanCache = [];
    private array $satuanCache = [];
    private int $createdMenu = 0;
    private int $createdBahan = 0;
    private int $createdSatuan = 0;
    private array $errors = [];
    private string $dineinPath = '';
    private string $nasiboxPath = '';
    private string $cateringPath = '';

    private array $dineInSections = [
        'Paket Nasi Liwet', 'Paket Nasi Ayam', 'Paket Nasi Ayam Kampung', 'Paket Nasi Bebek',
        'Sate', 'Sop', 'Gorengan', 'Lauk Satuan', 'Sayur dan Lalapan', 'Tambahan',
        'Cemilan', 'Minuman Jus', 'Minuman', 'Minuman Coffee', 'Minuman Non-Coffee',
    ];

    private array $sectionKategori = [
        'Paket Nasi Liwet' => 1,
        'Paket Nasi Ayam' => 20,
        'Paket Nasi Ayam Kampung' => 21,
        'Paket Nasi Bebek' => 22,
        'Sate' => 5,
        'Sop' => 6,
        'Gorengan' => 7,
        'Lauk Satuan' => 8,
        'Sayur dan Lalapan' => 9,
        'Tambahan' => 10,
        'Cemilan' => 11,
        'Minuman Jus' => 26,
        'Minuman' => 27,
        'Minuman Coffee' => 14,
        'Minuman Non-Coffee' => 15,
    ];

    private array $satuanAlias = [
        'gram' => 'Gram',
        'ml' => 'Mililiter',
        'lembar' => 'Lembar',
        'batang' => 'Batang',
        'butir' => 'Butir',
        'buah' => 'Buah',
        'cup' => 'Cup',
        'botol' => 'Botol',
        'sachet' => 'Sachet',
        'porsi' => 'Porsi',
    ];

    private array $satuanSingkatan = [
        'gram' => 'g',
        'ml' => 'ml',
        'lembar' => 'lbr',
        'batang' => 'btg',
        'butir' => 'btr',
        'buah' => 'bh',
        'cup' => 'cup',
        'botol' => 'btl',
        'sachet' => 'sct',
        'porsi' => 'prsi',
    ];

    private array $bahanAlias = [
        'Air' => 'Air galon',
        'Air Asam Jawa' => 'Asem',
        'Air Mineral' => 'Air mineral kemasan',
        'Air Mineral (kemasan)' => 'Air mineral kemasan',
        'Air Mineral 330 ml' => 'Air mineral kemasan',
        'Alpukat' => 'Alpukat',
        'Ati Ampela Ayam' => 'Ati ayam',
        'Ayam' => 'Ayam broiler',
        'Ayam Broiler' => 'Ayam broiler',
        'Ayam Kampung' => 'Ayam kampung',
        'Bakso Sapi' => 'Bakso sapi',
        'Bawang Bombay' => 'Bawang bombay',
        'Bawang Merah' => 'Bawang merah',
        'Bawang Putih' => 'Bawang putih',
        'Bawang Putih Bubuk' => 'Bawang putih bubuk',
        'Bawang Putih Goreng' => 'Bawang goreng',
        'Beras Liwet' => 'Beras',
        'Beras Putih' => 'Beras',
        'Brokoli' => 'Brokoli',
        'Buah Naga' => 'Buah naga',
        'Bubuk Milo' => 'Milo',
        'Buncis' => 'Buncis',
        'Cabai' => 'Cabe keriting',
        'Cabai Merah' => 'Cabe keriting',
        'Cabai Merah (giling)' => 'Cabe keriting',
        'Cabai Merah Giling' => 'Cabe keriting',
        'Cabai Merah Keriting' => 'Cabe keriting',
        'Cabai Rawit' => 'Cengek merah',
        'Cabai Rawit Merah' => 'Cengek merah',
        'Dada Ayam Fillet' => 'Ayam broiler',
        'Daun Bawang' => 'Bawang daun',
        'Daun Jeruk' => 'Daun jeruk',
        'Daun Kemangi' => 'Kemangi',
        'Daun Salam' => 'Daun salam',
        'Daging Bebek' => 'Bebek',
        'Daging Sapi' => 'Daging sapi',
        'Daging Sapi (has/potong dadu)' => 'Daging sapi',
        'Es Batu' => 'Es batu',
        'Es Krim Siap Pakai' => 'Es krim',
        'Fillet Dori' => 'Ikan dori',
        'Garam' => 'Garam halus',
        'Gula' => 'Gula putih',
        'Gula Merah' => 'Gula kawung',
        'Gula Merah / Gula Aren' => 'Gula kawung',
        'Gula Pasir' => 'Gula putih',
        'Ikan' => 'Ikan nila',
        'Ikan Lele' => 'Ikan lele',
        'Ikan Nila / Mas' => 'Ikan nila',
        'Ikan Peda Asin' => 'Peda',
        'Ikan Sepat Asin' => 'Sepat',
        'Jahe' => 'Jahe',
        'Jambu' => 'Jambu',
        'Jamur Kuping' => 'Jamur kuping',
        'Jengkol' => 'Jengkol',
        'Jeruk' => 'Jeruk',
        'Jeruk Nipis' => 'Jeruk purut',
        'Kacang Panjang' => 'Kacang panjang',
        'Kacang Tanah' => 'Kacang tanah',
        'Kacang Tanah (goreng)' => 'Kacang tanah',
        'Kaldu Ayam' => 'Royco ayam',
        'Kaldu Bubuk' => 'Royco ayam',
        'Kaldu Sapi' => 'Royco sapi',
        'Kangkung' => 'Kangkung',
        'Kecap Asin' => 'Kecap asin',
        'Kecap Manis' => 'Kecap manis',
        'Kemangi' => 'Kemangi',
        'Kencur' => 'Cikur',
        'Kentang' => 'Kentang',
        'Kerupuk' => 'Kerupuk udang',
        'Ketumbar' => 'Ketumbar',
        'Ketumbar Bubuk' => 'Ketumbar',
        'Kikil Sapi' => 'Kikil sapi',
        'Kol' => 'Kol',
        'Kunyit' => 'Kunyit',
        'Kunyit Bubuk' => 'Kunyit',
        'Lengkuas' => 'Laja',
        'Mangga' => 'Mangga',
        'Mangga Muda' => 'Mangga',
        'Margarin' => 'Mentega',
        'Mayones' => 'Mayones',
        'Melon' => 'Melon',
        'Merica' => 'Ladaku',
        'Merica / Lada Bubuk' => 'Ladaku',
        'Mi Kuning Basah' => 'Mi kuning',
        'Minyak' => 'Minyak',
        'Minyak Goreng' => 'Minyak',
        'Nanas' => 'Nanas',
        'Oncom' => 'Oncom',
        'Pala Bubuk' => 'Pala',
        'Paprika' => 'Paprika',
        'Pete' => 'Pete',
        'Santan' => 'Santan',
        'Santan Kental' => 'Santan',
        'Saus Sambal' => 'Saos sambal',
        'Saus Tomat' => 'Saos tomat',
        'Sayuran Hijau / Jukut' => 'Surawung',
        'Selada' => 'Selada',
        'Seledri' => 'Seledri',
        'Serai' => 'Sereh',
        'Sirsak' => 'Sirsak',
        'Sosis' => 'Sosis ayam',
        'Soun' => 'Soun',
        'Stroberi' => 'Strawberry',
        'Susu Cair' => 'Susu putih',
        'Susu Kental Manis' => 'Susu kental manis',
        'Susu Segar' => 'Susu putih',
        'Tahu' => 'Tahu',
        'Tahu Goreng' => 'Tahu',
        'Tauge' => 'Toge',
        'Telur Ayam' => 'Telur',
        'Tempe' => 'Tempe',
        'Tempe Tipis Khusus Mendoan' => 'Tempe',
        'Tepung Maizena' => 'Maizena',
        'Tepung Tapioka' => 'Tapioka',
        'Tepung Terigu' => 'Tepung terigu',
        'Terasi' => 'Terasi',
        'Timun' => 'Bonteng',
        'Tomat' => 'Tomat',
        'Wijen Sangrai' => 'Biji wijen',
        'Wortel' => 'Wortel',
    ];

    private array $bahanBaru = [
        'Anggur' => ['Anggur', 1],
        'Apel' => ['Apel', 1],
        'Bakso Ikan' => ['Bakso ikan', 2],
        'Bengkuang' => ['Bengkuang', 1],
        'Beras Pulen (Pandan Wangi)' => ['Beras pulen', 4],
        'Bubuk Matcha' => ['Bubuk matcha', 6],
        'Bumbu Kacang' => ['Bumbu kacang', 3],
        'Bumbu Kacang / Saus Kacang' => ['Bumbu kacang', 3],
        'Cuka' => ['Cuka', 3],
        'Daging Campur / Jeroan' => ['Daging campur / jeroan', 2],
        'Daging Kambing' => ['Daging kambing', 2],
        'Daun Kunyit' => ['Daun kunyit', 1],
        'Gula Cair (simple syrup)' => ['Gula cair', 3],
        'Iga Sapi' => ['Iga sapi', 2],
        'Jagung Manis' => ['Jagung manis', 1],
        'Jeruk Limau' => ['Jeruk limau', 1],
        'Kedondong' => ['Kedondong', 1],
        'Keju Parut' => ['Keju parut', 5],
        'Kopi Bubuk Arabika' => ['Kopi bubuk arabika', 6],
        'Kopi Bubuk Espresso' => ['Kopi bubuk espresso', 6],
        'Kopi Bubuk Robusta' => ['Kopi bubuk robusta', 6],
        'Kopi Sachet' => ['Kopi sachet', 6],
        'Kulit Ayam' => ['Kulit ayam', 2],
        'Kerupuk Mentah' => ['Kerupuk mentah', 5],
        'Kerupuk Udang Mentah' => ['Kerupuk udang mentah', 5],
        'Pepaya / buah tersedia' => ['Pepaya', 1],
        'Puding Siap Pakai' => ['Puding siap pakai', 5],
        'Sambal Oncom Pedas' => ['Sambal oncom pedas', 3],
        'Sawi' => ['Sawi', 1],
        'Semangka' => ['Semangka', 1],
        'Sirup Gula Aren (kopi)' => ['Sirup gula aren', 3],
        'Sirup Karamel' => ['Sirup karamel', 3],
        'Sirup Vanilla' => ['Sirup vanilla', 3],
        'Susu Bubuk' => ['Susu bubuk', 6],
        'Tahu Sumedang' => ['Tahu sumedang', 5],
        'Telur Puyuh' => ['Telur puyuh', 2],
        'Tepung Beras' => ['Tepung beras', 4],
        'Tepung Bumbu Serbaguna' => ['Tepung bumbu serbaguna', 4],
        'Tepung Serbaguna' => ['Tepung bumbu serbaguna', 4],
        'Tomat Ceri' => ['Tomat ceri', 1],
        'Tusuk Sate' => ['Tusuk sate', 4],
    ];

    private array $dineInMap = [
        'Paket Nasi Liwet 1' => 'Paket Bancakan 1',
        'Paket Nasi Liwet 2' => 'Paket Bancakan 2',
        'Paket Nasi Liwet 3' => 'Paket Bancakan 3',
        'Nasi Ayam Goreng' => 'Nasi Ayam Goreng',
        'Nasi Ayam Bakar' => 'Nasi Ayam Bakar',
        'Liwet Ayam Goreng' => 'Nasi Liwet Ayam Goreng',
        'Liwet Ayam Bakar' => 'Nasi Liwet Ayam Bakar',
        'Nasi Ayam Penyet Goreng' => 'Nasi Ayam Goreng Penyet',
        'Nasi Ayam Penyet Bakar' => 'Nasi Ayam Bakar Penyet',
        'Liwet Ayam Penyet Goreng' => 'Nasi Liwet Ayam Goreng Penyet',
        'Liwet Ayam Penyet Bakar' => 'Nasi Liwet Ayam Bakar Penyet',
        'Nasi Tutug Oncom Ayam Goreng' => 'Nasi Tutug Oncom Ayam Goreng',
        'Nasi Tutug Oncom Ayam Bakar' => 'Nasi Tutug Oncom Ayam Bakar',
        'Nasi Ayam Kampung Goreng' => 'Nasi Ayam Kampung Goreng',
        'Nasi Ayam Kampung Bakar' => 'Nasi Ayam Kampung Bakar',
        'Liwet Ayam Kampung Goreng' => 'Nasi Liwet Ayam Kampung Goreng',
        'Liwet Ayam Kampung Bakar' => 'Nasi Liwet Ayam Kampung Bakar',
        'Nasi Ayam Kampung Penyet Goreng' => 'Nasi Ayam Kampung Goreng Penyet',
        'Nasi Ayam Kampung Penyet Bakar' => 'Nasi Ayam Kampung Bakar Penyet',
        'Liwet Ayam Kampung Penyet Goreng' => 'Nasi Liwet Ayam Kampung Goreng Penyet',
        'Liwet Ayam Kampung Penyet Bakar' => 'Nasi Liwet Ayam Kampung Bakar Penyet',
        'Nasi Tutug Oncom Ayam Kampung Goreng' => 'Nasi Tutug Oncom Ayam Kampung Goreng',
        'Nasi Tutug Oncom Ayam Kampung Bakar' => 'Nasi Tutug Oncom Ayam Kampung Bakar',
        'Nasi Bebek Goreng' => 'Nasi Bebek Goreng',
        'Nasi Bebek Bakar' => 'Nasi Bebek Bakar',
        'Liwet Bebek Penyet Goreng' => 'Nasi Liwet Bebek Goreng Penyet',
        'Liwet Bebek Penyet Bakar' => 'Nasi Liwet Bebek Bakar Penyet',
        'Nasi Bebek Penyet Goreng' => 'Nasi Bebek Goreng Penyet',
        'Nasi Bebek Penyet Bakar' => 'Nasi Bebek Bakar Penyet',
        'Liwet Bebek Goreng' => 'Nasi Liwet Bebek Goreng',
        'Liwet Bebek Bakar' => 'Nasi Liwet Bebek Bakar',
        'Nasi Tutug Oncom Bebek Goreng' => 'Nasi Tutug Oncom Bebek Goreng',
        'Nasi Tutug Oncom Bebek Bakar' => 'Nasi Tutug Oncom Bebek Bakar',
        'Sate Sapi' => null,
        'Sate Kambing' => null,
        'Sate Ayam' => null,
        'Sate Jando' => null,
        'Sop Iga Sapi' => null,
        'Sop Iga Sapi + Nasi' => null,
        'Kulit Goreng Jumbo' => null,
        'Kulit Goreng Jumbo + Nasi' => null,
        'Ayam Bakar' => null,
        'Ayam Kampung' => 'Ayam Kampung',
        'Ayam Broiler' => 'Ayam Broiler',
        'Bebek' => 'Bebek',
        'Ikan' => 'Ikan',
        'Tahu' => 'Tahu',
        'Tempe' => 'Tempe',
        'Peda' => 'Ikan Peda',
        'Sepat' => 'Ikan Sepat',
        'Jengkol' => 'Jengkol',
        'Pete' => 'Pete',
        'Kol Goreng' => 'Kol Goreng',
        'Jukut Goreng' => 'Jukut Goreng',
        'Karedok' => 'Karedok',
        'Lotek' => 'Lotek',
        'Pencok Kacang' => 'Pencok Kacang',
        'Nasi Putih' => 'Nasi Putih',
        'Nasi Liwet' => 'Nasi Liwet',
        'Nasi Tutug Oncom' => 'Nasi Tutug Oncom',
        'Nasi Liwet Pulen' => null,
        'Nasi Tutug Oncom Pulen' => null,
        'Sambal' => 'Sambal',
        'Lalapan + Sambal' => 'Lalab dan Sambal',
        'Tahu Gejrot' => null,
        'Tahu Sumedang' => null,
        'Cireng Rujak' => null,
        'Mendoan' => null,
        'Jus Sirsak' => 'Jus Sirsak',
        'Jus Mangga' => 'Jus Mangga',
        'Jus Jeruk' => 'Jus Jeruk',
        'Jus Melon' => 'Jus Melon',
        'Jus Jambu' => 'Jus Jambu',
        'Jus Stroberi' => 'Jus Strawberry',
        'Jus Buah Naga' => 'Jus Buah Naga',
        'Jus Alpukat' => 'Jus Alpukat',
        'Bandrek' => 'Bandrek',
        'Bajigur' => 'Bajigur',
        'Bandrek Susu' => 'Bandrek Susu',
        'Bajigur Susu' => 'Bajigur Susu',
        'Susu Putih' => 'Susu Putih',
        'Susu Cokelat' => 'Susu Cokelat',
        'Milo (Panas/Dingin)' => '__MILO__',
        'Kopi Kapal Api' => 'Kopi Kapal Api',
        'Kopi Good Day' => 'Kopi Good Day',
        'Kopi Luwak' => 'Kopi Luwak',
        'Kopi Indocafe' => 'Kopi Indocafe',
        'Kopi ABC Susu' => 'Kopi ABC Susu',
        'Air Mineral' => 'Air Mineral',
        'Es Kopi Susu' => null,
        'Es Kopi Susu Vanilla' => null,
        'Es Kopi Susu Gula Aren' => null,
        'Americano' => null,
        'Cappuccino' => 'Cappuccino',
        'Café Latte' => null,
        'Espresso' => null,
        'Kopi Tubruk Arabika' => null,
        'Kopi Tubruk Robusta' => null,
        'Caramel Macchiato' => null,
        'Hot Green Matcha' => null,
    ];

    public function run(): void
    {
        $this->info('Memulai impor resep dari dokumen...');

        $base = base_path('docs');
        $this->dineinPath = $base . '/daftar_menu_dinein_resep.md';
        $this->nasiboxPath = $base . '/daftar_menu_nasibox_resep.md';
        $this->cateringPath = $base . '/daftar_menu_catering_resep.md';

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->syncDineIn();
        $this->syncNasiBox();
        $this->syncCatering();
        $this->alignPaketStruktur();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->validasi();

        $this->info("Selesai. Menu baru: {$this->createdMenu}, Bahan baru: {$this->createdBahan}, Satuan baru: {$this->createdSatuan}");
        foreach ($this->errors as $e) {
            $this->warn($e);
        }
    }

    private function info(string $m): void
    {
        if ($this->command) {
            $this->command->info($m);
        } else {
            echo $m . PHP_EOL;
        }
    }

    private function warn(string $m): void
    {
        if ($this->command) {
            $this->command->warn($m);
        } else {
            echo 'WARN: ' . $m . PHP_EOL;
        }
    }

    private function parseResepFile(string $path, array $opts): array
    {
        $dineIn = $opts['dineIn'] ?? false;
        $startMarker = $opts['startMarker'] ?? null;
        $stopAt = $opts['stopAt'] ?? null;
        $items = [];
        $cur = null;
        $section = null;
        $started = $startMarker === null;
        $step = 0;
        $pending = null;

        foreach (file($path, FILE_IGNORE_NEW_LINES) as $raw) {
            $line = trim($raw);
            if ($line === '') {
                continue;
            }
            if (!$started) {
                if ($line === $startMarker) {
                    $started = true;
                }
                continue;
            }
            if ($stopAt !== null && $line === $stopAt) {
                break;
            }
            if ($dineIn && in_array($line, $this->dineInSections, true)) {
                if ($cur) {
                    $items[] = $cur;
                    $cur = null;
                }
                $section = $line;
                $step = 0;
                $pending = null;
                continue;
            }
            if (preg_match('/^\d+\.\s+(.+)$/', $line, $m)) {
                if ($cur) {
                    $items[] = $cur;
                }
                $cur = ['nama' => $m[1], 'harga' => null, 'basis' => null, 'section' => $section, 'bahan' => []];
                $step = 0;
                $pending = null;
                continue;
            }
            if ($cur === null) {
                continue;
            }
            if (preg_match('/^Harga:\s*(.+)$/i', $line, $m)) {
                $cur['harga'] = $this->parseHarga($m[1]);
                $step = 0;
                $pending = null;
                continue;
            }
            if (stripos($line, 'Basis resep:') !== false) {
                $cur['basis'] = trim(str_ireplace('Basis resep:', '', $line));
                $step = 0;
                $pending = null;
                continue;
            }
            if ($line === 'Bahan Baku') {
                $step = 1;
                continue;
            }
            if ($line === 'Jumlah' && $step === 1) {
                $step = 2;
                continue;
            }
            if ($line === 'Satuan' && $step === 2) {
                $step = 3;
                continue;
            }
            if ($step === 3) {
                if (preg_match('/^[\d.,]+$/', $line)) {
                    continue;
                }
                if (strlen($line) > 45) {
                    continue;
                }
                $pending = $line;
                $step = 4;
                continue;
            }
            if ($step === 4) {
                if (preg_match('/^([\d.,]+)$/', $line, $m)) {
                    $num = (float) str_replace(',', '.', $m[1]);
                    $cur['bahan'][] = ['nama' => $pending, 'jumlah' => $num, 'satuan' => null];
                    $step = 5;
                    continue;
                }
                if (strlen($line) <= 45) {
                    $cur['bahan'][] = ['nama' => $pending, 'jumlah' => null, 'satuan' => null];
                    $pending = $line;
                }
                continue;
            }
            if ($step === 5) {
                if ($line === 'Bahan Baku') {
                    $step = 1;
                    continue;
                }
                if (preg_match('/^[\d.,]+$/', $line)) {
                    continue;
                }
                $idx = count($cur['bahan']) - 1;
                $cur['bahan'][$idx]['satuan'] = $line;
                $step = 3;
            }
        }
        if ($cur) {
            $items[] = $cur;
        }

        return $items;
    }

    private function parseHarga(string $s): float
    {
        $digits = preg_replace('/[^0-9]/', '', $s);

        return (float) ($digits === '' ? 0 : $digits);
    }

    private function findMenu(string $name, ?int $preferJenis = null, bool $strict = false): ?Menu
    {
        $key = $name . '|' . ($preferJenis ?? '*') . ($strict ? '|strict' : '');
        if (array_key_exists($key, $this->menuCache)) {
            return $this->menuCache[$key];
        }
        $q = Menu::where('nama_menu', $name);
        if ($preferJenis !== null) {
            if ($strict) {
                $q->where('jenis_menu_id', $preferJenis);
            } else {
                $q->orderByRaw('jenis_menu_id = ? DESC', [$preferJenis]);
            }
        }
        $menu = $q->orderBy('id')->first();
        $this->menuCache[$key] = $menu;

        return $menu;
    }

    private function createMenu(string $nama, int $jenis, int $kategori, float $harga): Menu
    {
        $menu = Menu::create([
            'jenis_menu_id' => $jenis,
            'kategori_menu_id' => $kategori,
            'nama_menu' => $nama,
            'harga_jual' => $harga,
            'deskripsi' => null,
            'status_aktif' => 1,
        ]);
        $this->createdMenu++;

        return $menu;
    }

    private function satuanId(string $nama): int
    {
        $nama = strtolower(trim($nama));
        if ($nama === '') {
            $nama = 'gram';
        }
        if (isset($this->satuanCache[$nama])) {
            return $this->satuanCache[$nama];
        }
        $dbNama = $this->satuanAlias[$nama] ?? null;
        if ($dbNama === null) {
            $dbNama = ucfirst($nama);
        }
        $satuan = Satuan::where('nama_satuan', $dbNama)->first();
        if (!$satuan) {
            $satuan = Satuan::create([
                'nama_satuan' => $dbNama,
                'singkatan' => $this->satuanSingkatan[$nama] ?? strtolower(substr($dbNama, 0, 3)),
            ]);
            $this->createdSatuan++;
        }
        $this->satuanCache[$nama] = $satuan->id;

        return $satuan->id;
    }

    private function bahanId(string $nama): ?int
    {
        $nama = trim($nama);
        if ($nama === '' || strlen($nama) > 45) {
            return null;
        }
        if (isset($this->bahanCache[$nama])) {
            return $this->bahanCache[$nama];
        }
        $dbNama = $this->bahanAlias[$nama] ?? null;
        $kategori = 4;
        if ($dbNama === null && isset($this->bahanBaru[$nama])) {
            [$dbNama, $kategori] = $this->bahanBaru[$nama];
        }
        if ($dbNama === null) {
            $dbNama = $nama;
        }
        $bahan = BahanBaku::where('nama_bahan', $dbNama)->first();
        if (!$bahan) {
            $bahan = BahanBaku::create([
                'kategori_bahan_baku_id' => $kategori,
                'satuan_id' => $this->satuanId('gram'),
                'nama_bahan' => $dbNama,
                'stok_minimal' => 0,
                'harga_satuan' => 0,
                'jenis_peruntukan' => 'Semua',
                'status_aktif' => 1,
            ]);
            $this->createdBahan++;
        }
        $this->bahanCache[$nama] = $bahan->id;

        return $bahan->id;
    }

    private function writeResep(Menu $menu, array $bahan): void
    {
        ResepMenu::where('menu_id', $menu->id)->delete();

        $merged = [];
        foreach ($bahan as $b) {
            $bid = $this->bahanId($b['nama'] ?? '');
            if ($bid === null) {
                continue;
            }
            $sid = $this->satuanId($b['satuan'] ?? 'gram');
            $jml = (float) ($b['jumlah'] ?? 0);
            if (!isset($merged[$bid])) {
                $merged[$bid] = ['bahan_baku_id' => $bid, 'jumlah' => $jml, 'satuan_id' => $sid];
            } else {
                $merged[$bid]['jumlah'] += $jml;
            }
        }
        foreach ($merged as $row) {
            ResepMenu::create([
                'menu_id' => $menu->id,
                'bahan_baku_id' => $row['bahan_baku_id'],
                'jumlah' => $row['jumlah'],
                'satuan_id' => $row['satuan_id'],
                'keterangan' => null,
                'dikonfirmasi' => 1,
            ]);
        }
    }

    private function syncDineIn(): void
    {
        $items = $this->parseResepFile($this->dineinPath, ['dineIn' => true, 'stopAt' => 'Validasi Database']);

        foreach ($items as $it) {
            $target = array_key_exists($it['nama'], $this->dineInMap) ? $this->dineInMap[$it['nama']] : $it['nama'];

            if ($target === '__MILO__') {
                foreach (['Milo Panas', 'Milo Dingin'] as $nm) {
                    $menu = $this->findMenu($nm, 1, true);
                    if ($menu) {
                        $this->writeResep($menu, $it['bahan']);
                    } else {
                        $this->errors[] = "Menu '{$nm}' tidak ditemukan untuk resep Milo";
                    }
                }
                continue;
            }

            if ($target === null) {
                $kategori = $this->sectionKategori[$it['section']] ?? 17;
                $menu = $this->createMenu($it['nama'], 1, $kategori, $it['harga'] ?? 0);
            } else {
                $menu = $this->findMenu($target, 1, true);
                if (!$menu) {
                    $this->errors[] = "Menu Dine-In '{$target}' (dari dokumen '{$it['nama']}') tidak ditemukan";
                    continue;
                }
            }

            $this->writeResep($menu, $it['bahan']);
        }

        $this->info('Dine-In : ' . count($items) . ' resep diproses');
    }

    private function syncNasiBox(): void
    {
        $items = $this->parseResepFile($this->nasiboxPath, [
            'startMarker' => 'Resep Menu Nasi Box',
            'stopAt' => 'Contoh Relasi Paket ke Resep',
        ]);
        $shared = ['Nasi Putih', 'Nasi Liwet', 'Sambal', 'Karedok', 'Air Mineral'];

        foreach ($items as $it) {
            if (in_array($it['nama'], $shared, true)) {
                continue;
            }
            $menu = $this->findMenu($it['nama'], 3, true);
            if ($menu) {
                continue;
            }
            $menu = $this->createMenu($it['nama'], 3, 17, 0);
            $this->writeResep($menu, $it['bahan']);
        }

        $this->info('Nasi Box : ' . count($items) . ' item diproses, ' . $this->createdMenu . ' menu baru (dine-in sudah ada)');
    }

    private function syncCatering(): void
    {
        $items = $this->parseResepFile($this->cateringPath, [
            'startMarker' => 'Resep Menu Catering',
            'stopAt' => 'Contoh Relasi Paket ke Resep',
        ]);
        $shared = ['Nasi Putih', 'Air Mineral'];

        foreach ($items as $it) {
            if (in_array($it['nama'], $shared, true)) {
                continue;
            }
            $menu = $this->findMenu($it['nama'], 2, true);
            if ($menu) {
                continue;
            }
            $menu = $this->createMenu($it['nama'], 2, 16, 0);
            $this->writeResep($menu, $it['bahan']);
        }

        $this->info('Catering : ' . count($items) . ' item diproses');
    }

    private function alignPaketStruktur(): void
    {
        // Catering: MN101 (Paket A), MN102 (Paket B)
        $cateringTarget = [
            101 => [
                ['nama' => 'Nasi', 'tipe' => 'wajib', 'terkait' => 'Nasi Putih', 'opsi' => []],
                ['nama' => 'Aneka Sup', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Sup Kimlo', 'Sup Bakso', 'Sup Ayam Sosis']],
                ['nama' => 'Aneka Olahan Daging Sapi', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Sapi Teriyaki', 'Rendang', 'Bistik']],
                ['nama' => 'Aneka Olahan Tambahan', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Dori Asam Manis', 'Dori Saus Mentega', 'Sambal Goreng Ati Kentang']],
                ['nama' => 'Sayuran', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Salad Buah', 'Salad Sayuran', 'Gado-Gado', 'Rujak Buah']],
                ['nama' => 'Pelengkap', 'tipe' => 'semua_didapat', 'opsi' => ['Kerupuk Udang', 'Air Mineral']],
                ['nama' => 'Stall', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Bakso Tahu', 'Mi Kocok']],
                ['nama' => 'Dessert', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Buah Potong', 'Es Krim']],
            ],
            102 => [
                ['nama' => 'Nasi', 'tipe' => 'wajib', 'terkait' => 'Nasi Putih', 'opsi' => []],
                ['nama' => 'Aneka Sup', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Sup Kimlo', 'Sup Bakso', 'Sup Sosis']],
                ['nama' => 'Aneka Olahan Ayam', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Ayam Teriyaki', 'Ayam Suwir', 'Ayam Rica-Rica']],
                ['nama' => 'Aneka Olahan Tambahan', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Dori Asam Manis', 'Dori Saus Mentega', 'Sambal Goreng Ati Kentang']],
                ['nama' => 'Sayuran', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Salad Buah', 'Salad Sayuran', 'Gado-Gado', 'Rujak Buah']],
                ['nama' => 'Pelengkap', 'tipe' => 'semua_didapat', 'opsi' => ['Kerupuk Udang', 'Air Mineral']],
                ['nama' => 'Stall', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Bakso Tahu', 'Mi Kocok']],
                ['nama' => 'Dessert', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Buah Potong', 'Es Krim']],
            ],
        ];

        // Nasi Box: MN103 (105), MN104 (106), MN105 (107), MN106 (108), MN107 (109)
        $nasiBoxTarget = [
            105 => [
                ['nama' => 'Nasi', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Nasi Putih', 'Nasi Liwet']],
                ['nama' => 'Lauk Ayam', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Ayam Goreng', 'Ayam Bakar']],
                ['nama' => 'Lauk Ikan', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Ikan Goreng', 'Lele Goreng']],
                ['nama' => 'Lauk Tambahan', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Telur Balado', 'Kentang Balado']],
                ['nama' => 'Sayuran', 'tipe' => 'wajib', 'terkait' => 'Karedok', 'opsi' => []],
                ['nama' => 'Lalapan', 'tipe' => 'wajib', 'terkait' => 'Lalapan', 'opsi' => []],
                ['nama' => 'Pelengkap', 'tipe' => 'semua_didapat', 'opsi' => ['Sambal', 'Kerupuk']],
                ['nama' => 'Buah', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Melon', 'Semangka', 'Jeruk']],
                ['nama' => 'Makanan Penutup', 'tipe' => 'wajib', 'terkait' => 'Puding', 'opsi' => []],
                ['nama' => 'Minuman', 'tipe' => 'wajib', 'terkait' => 'Air Mineral', 'opsi' => []],
            ],
            106 => [
                ['nama' => 'Nasi', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Nasi Putih', 'Nasi Liwet']],
                ['nama' => 'Lauk Utama', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Ayam Goreng', 'Ayam Bakar', 'Ikan Goreng']],
                ['nama' => 'Lauk Tambahan', 'tipe' => 'semua_didapat', 'opsi' => ['Tempe Goreng', 'Tahu Goreng']],
                ['nama' => 'Sayuran', 'tipe' => 'wajib', 'terkait' => 'Tumis Buncis Wortel', 'opsi' => []],
                ['nama' => 'Lalapan', 'tipe' => 'wajib', 'terkait' => 'Lalapan', 'opsi' => []],
                ['nama' => 'Pelengkap', 'tipe' => 'semua_didapat', 'opsi' => ['Sambal', 'Kerupuk']],
                ['nama' => 'Buah', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Melon', 'Semangka', 'Jeruk']],
                ['nama' => 'Minuman', 'tipe' => 'wajib', 'terkait' => 'Air Mineral', 'opsi' => []],
            ],
            107 => [
                ['nama' => 'Nasi', 'tipe' => 'wajib', 'terkait' => 'Nasi Putih', 'opsi' => []],
                ['nama' => 'Lauk Utama', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Ayam Goreng', 'Ayam Bakar', 'Ikan Goreng']],
                ['nama' => 'Lauk Tambahan', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Tempe Goreng', 'Tahu Goreng']],
                ['nama' => 'Sayuran', 'tipe' => 'wajib', 'terkait' => 'Cah Brokoli Wortel', 'opsi' => []],
                ['nama' => 'Lalapan', 'tipe' => 'wajib', 'terkait' => 'Lalapan', 'opsi' => []],
                ['nama' => 'Pelengkap', 'tipe' => 'semua_didapat', 'opsi' => ['Sambal', 'Kerupuk']],
                ['nama' => 'Buah', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Melon', 'Semangka', 'Jeruk']],
                ['nama' => 'Minuman', 'tipe' => 'wajib', 'terkait' => 'Air Mineral', 'opsi' => []],
            ],
            108 => [
                ['nama' => 'Nasi', 'tipe' => 'wajib', 'terkait' => 'Nasi Putih', 'opsi' => []],
                ['nama' => 'Lauk Utama', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Ayam Goreng', 'Ayam Bakar']],
                ['nama' => 'Lauk Tambahan', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Tempe Goreng', 'Tahu Goreng']],
                ['nama' => 'Sayuran', 'tipe' => 'wajib', 'terkait' => 'Capcay', 'opsi' => []],
                ['nama' => 'Lalapan', 'tipe' => 'wajib', 'terkait' => 'Lalapan', 'opsi' => []],
                ['nama' => 'Pelengkap', 'tipe' => 'semua_didapat', 'opsi' => ['Sambal', 'Kerupuk']],
                ['nama' => 'Minuman', 'tipe' => 'wajib', 'terkait' => 'Air Mineral', 'opsi' => []],
            ],
            109 => [
                ['nama' => 'Nasi', 'tipe' => 'wajib', 'terkait' => 'Nasi Putih', 'opsi' => []],
                ['nama' => 'Lauk Utama', 'tipe' => 'pilihan', 'min' => 1, 'max' => 1, 'opsi' => ['Ayam Goreng', 'Ayam Bakar']],
                ['nama' => 'Lalapan', 'tipe' => 'wajib', 'terkait' => 'Lalapan', 'opsi' => []],
                ['nama' => 'Pelengkap', 'tipe' => 'semua_didapat', 'opsi' => ['Sambal', 'Kerupuk']],
                ['nama' => 'Minuman', 'tipe' => 'wajib', 'terkait' => 'Air Mineral', 'opsi' => []],
            ],
        ];

        foreach ($cateringTarget as $menuId => $target) {
            $this->applyTarget($menuId, $target, 2);
        }
        foreach ($nasiBoxTarget as $menuId => $target) {
            $this->applyTarget($menuId, $target, 3);
        }

        $this->info('Struktur paket catering & nasi box diselaraskan dengan dokumen.');
    }

    private function applyTarget(int $menuId, array $target, int $jenisPaket): void
    {
        $menu = Menu::find($menuId);
        if (!$menu) {
            $this->errors[] = "Paket menu_id {$menuId} tidak ditemukan";

            return;
        }

        $ids = ItemPaket::where('menu_id', $menuId)->pluck('id');
        if ($ids->isNotEmpty()) {
            PilihanItemPaket::whereIn('item_paket_id', $ids)->delete();
        }
        ItemPaket::where('menu_id', $menuId)->delete();

        $urutan = 1;
        foreach ($target as $t) {
            $terkait = null;
            if (!empty($t['terkait'])) {
                $tm = $this->findMenu($t['terkait'], $jenisPaket);
                if (!$tm) {
                    $tm = $this->findMenu($t['terkait'], null);
                }
                if ($tm) {
                    $terkait = $tm->id;
                } else {
                    $this->errors[] = "Menu terkait '{$t['terkait']}' tidak ditemukan untuk paket {$menu->id_menu}";
                }
            }

            $ip = ItemPaket::create([
                'menu_id' => $menuId,
                'menu_id_terkait' => $terkait,
                'jumlah' => 1,
                'satuan_sajian' => null,
                'nama_item' => $t['nama'],
                'tipe_item' => $t['tipe'],
                'minimum_pilihan' => $t['min'] ?? 0,
                'maksimum_pilihan' => $t['max'] ?? 0,
                'urutan' => $urutan++,
            ]);

            $u = 1;
            foreach ($t['opsi'] ?? [] as $opsiNama) {
                $om = $this->findMenu($opsiNama, $jenisPaket);
                if (!$om) {
                    $om = $this->findMenu($opsiNama, null);
                }
                if (!$om) {
                    $this->errors[] = "Opsi '{$opsiNama}' (komponen {$t['nama']}, paket {$menu->id_menu}) tidak ditemukan";
                    continue;
                }
                PilihanItemPaket::create([
                    'item_paket_id' => $ip->id,
                    'nama_pilihan' => $om->nama_menu,
                    'menu_id' => $om->id,
                    'jumlah' => 1,
                    'satuan_sajian' => null,
                    'urutan' => $u++,
                ]);
            }
        }
    }

    private function validasi(): void
    {
        $nullMenu = PilihanItemPaket::whereNull('menu_id')->count();
        $this->info("pilihan_item_paket menu_id NULL : {$nullMenu}");

        $wajibKosong = ItemPaket::where('tipe_item', 'wajib')->whereNull('menu_id_terkait')->count();
        $this->info("item_paket 'wajib' tanpa menu_id_terkait : {$wajibKosong}");

        $opsiTanpaResep = 0;
        foreach (PilihanItemPaket::whereNotNull('menu_id')->get() as $p) {
            if (!$p->menu || !$p->menu->resep_menu()->exists()) {
                $opsiTanpaResep++;
            }
        }
        $this->info("opsi yang menunjuk menu tanpa resep : {$opsiTanpaResep}");

        $this->info('Menu  : ' . Menu::count());
        $this->info('Resep : ' . ResepMenu::count());
        $this->info('Bahan : ' . BahanBaku::count());
        $this->info('Satuan: ' . Satuan::count());
    }
}
