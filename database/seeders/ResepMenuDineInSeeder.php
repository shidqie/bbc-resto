<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\Satuan;
use App\Models\ResepMenu;
use Illuminate\Support\Facades\DB;

class ResepMenuDineInSeeder extends Seeder
{
    public function run(): void
    {
        $menuMap  = Menu::where('jenis_menu_id', 1)->pluck('id', 'nama_menu')->toArray();
        $bahanMap = BahanBaku::pluck('id', 'nama_bahan')->toArray();
        $satuanMap = Satuan::pluck('id', 'nama_satuan')->toArray();

        $s = [
            'g'      => $satuanMap['Gram']      ?? 18,
            'ml'     => $satuanMap['Mililiter']  ?? 4,
            'lembar' => $satuanMap['Lembar']     ?? 13,
            'batang' => $satuanMap['Buah']       ?? 5,
            'pcs'    => $satuanMap['Pcs']        ?? 10,
            'sachet' => $satuanMap['Sachet']     ?? 14,
            'botol'  => $satuanMap['Botol']      ?? 9,
        ];

        // Hapus resep lama menu reguler
        DB::table('resep_menu')->whereIn('menu_id', array_values($menuMap))->delete();

        $add = function (string $nama, array $bahan) use ($menuMap, $bahanMap, $s) {
            $menuId = $menuMap[$nama] ?? null;
            if (!$menuId) { $this->command->warn("MENU SKIP: $nama"); return; }
            // Gabungkan bahan yang sama (misal Cabe tanjung 5g + 30g = 35g)
            $merged = [];
            foreach ($bahan as [$b, $j, $sat]) {
                $bid = $bahanMap[$b] ?? null;
                $sid = $s[strtolower(trim($sat))] ?? null;
                if (!$bid) { $this->command->warn("BAHAN SKIP: $b ($nama)"); continue; }
                if (!$sid) { $this->command->warn("SATUAN SKIP: $sat ($b)"); continue; }
                if (isset($merged[$bid])) {
                    $merged[$bid]['jumlah'] += $j;
                } else {
                    $merged[$bid] = ['bahan_baku_id'=>$bid, 'jumlah'=>$j, 'satuan_id'=>$sid];
                }
            }
            foreach ($merged as $row) {
                ResepMenu::create(['menu_id'=>$menuId,'bahan_baku_id'=>$row['bahan_baku_id'],'jumlah'=>$row['jumlah'],'satuan_id'=>$row['satuan_id'],'dikonfirmasi'=>1]);
            }
        };

        // NAMA BAHAN SESUAI DATABASE:
        // Garam halus (78), Minyak (128), Gula putih (115), Gula kawung (114)
        // Sereh (33), Cikur (12), Bonteng (6), Cengek merah (10)
        // Peda (54), Sepat (55), Toge (27), Kemangi (161), Mangga (162)
        // Cabe tanjung=sambal biasa, Cengek merah=cabe rawit, Cabe keriting=cabe merah keriting

        // --- 1. BANCAKAN ---
        $base = [['Beras',500,'g'],['Santan',150,'ml'],['Daun salam',5,'lembar'],['Sereh',2,'batang'],['Tahu',5,'pcs'],['Tempe',250,'g'],['Kangkung',500,'g'],['Jengkol',250,'g'],['Peda',250,'g'],['Bonteng',200,'g'],['Kol',150,'g'],['Kemangi',50,'g'],['Cabe tanjung',100,'g'],['Bawang merah',50,'g'],['Bawang putih',30,'g'],['Minyak',250,'ml'],['Garam halus',20,'g'],['Gula putih',20,'g']];
        $add('Paket Bancakan 1', array_merge($base, [['Ayam broiler',1000,'g'],['Ikan nila',500,'g']]));
        $add('Paket Bancakan 2', array_merge($base, [['Ayam kampung',1000,'g']]));
        $add('Paket Bancakan 3', array_merge($base, [['Bebek',1200,'g']]));

        // --- 2. NASI AYAM BROILER ---
        $add('Nasi Ayam Goreng', [['Beras',100,'g'],['Ayam broiler',150,'g'],['Bawang putih',5,'g'],['Kunyit',3,'g'],['Garam halus',3,'g'],['Minyak',20,'ml'],['Bonteng',30,'g'],['Kemangi',10,'g'],['Cabe tanjung',30,'g']]);
        $add('Nasi Ayam Bakar', [['Beras',100,'g'],['Ayam broiler',150,'g'],['Bawang putih',5,'g'],['Kecap manis',15,'ml'],['Bawang merah',5,'g'],['Garam halus',3,'g'],['Bonteng',30,'g'],['Kemangi',10,'g'],['Cabe tanjung',30,'g']]);
        $add('Nasi Liwet Ayam Goreng', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Ayam broiler',150,'g'],['Bawang putih',5,'g'],['Kunyit',3,'g'],['Minyak',20,'ml'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Ayam Bakar', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Ayam broiler',150,'g'],['Kecap manis',15,'ml'],['Bawang merah',5,'g'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Ayam Goreng Penyet', [['Beras',100,'g'],['Ayam broiler',150,'g'],['Bawang putih',5,'g'],['Minyak',20,'ml'],['Cengek merah',15,'g'],['Cabe tanjung',10,'g'],['Tomat',20,'g'],['Bawang merah',5,'g'],['Kemangi',40,'g']]);
        $add('Nasi Ayam Bakar Penyet', [['Beras',100,'g'],['Ayam broiler',150,'g'],['Kecap manis',15,'ml'],['Bawang merah',5,'g'],['Cengek merah',15,'g'],['Cabe tanjung',10,'g'],['Tomat',20,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Ayam Goreng Penyet', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Ayam broiler',150,'g'],['Minyak',20,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Ayam Bakar Penyet', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Ayam broiler',150,'g'],['Kecap manis',15,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Tutug Oncom Ayam Goreng', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Cabe tanjung',5,'g'],['Ayam broiler',150,'g'],['Minyak',20,'ml'],['Cabe tanjung',30,'g']]);
        $add('Nasi Tutug Oncom Ayam Bakar', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Cabe tanjung',5,'g'],['Ayam broiler',150,'g'],['Kecap manis',15,'ml'],['Cabe tanjung',30,'g']]);

        // --- 3. NASI AYAM KAMPUNG ---
        $add('Nasi Ayam Kampung Goreng', [['Beras',100,'g'],['Ayam kampung',180,'g'],['Bawang putih',5,'g'],['Kunyit',3,'g'],['Garam halus',3,'g'],['Minyak',20,'ml'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Ayam Kampung Bakar', [['Beras',100,'g'],['Ayam kampung',180,'g'],['Bawang putih',5,'g'],['Bawang merah',5,'g'],['Kecap manis',15,'ml'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Ayam Kampung Goreng', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Ayam kampung',180,'g'],['Minyak',20,'ml'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Ayam Kampung Bakar', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Ayam kampung',180,'g'],['Kecap manis',15,'ml'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Ayam Kampung Goreng Penyet', [['Beras',100,'g'],['Ayam kampung',180,'g'],['Minyak',20,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Ayam Kampung Bakar Penyet', [['Beras',100,'g'],['Ayam kampung',180,'g'],['Kecap manis',15,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Ayam Kampung Goreng Penyet', [['Beras',100,'g'],['Santan',30,'ml'],['Ayam kampung',180,'g'],['Minyak',20,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Ayam Kampung Bakar Penyet', [['Beras',100,'g'],['Santan',30,'ml'],['Ayam kampung',180,'g'],['Kecap manis',15,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Tutug Oncom Ayam Kampung Goreng', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Ayam kampung',180,'g'],['Minyak',20,'ml'],['Cabe tanjung',30,'g']]);
        $add('Nasi Tutug Oncom Ayam Kampung Bakar', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Ayam kampung',180,'g'],['Kecap manis',15,'ml'],['Cabe tanjung',30,'g']]);

        // --- 4. NASI BEBEK ---
        $add('Nasi Bebek Goreng', [['Beras',100,'g'],['Bebek',200,'g'],['Bawang putih',6,'g'],['Kunyit',3,'g'],['Garam halus',3,'g'],['Minyak',25,'ml'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Bebek Bakar', [['Beras',100,'g'],['Bebek',200,'g'],['Bawang putih',6,'g'],['Bawang merah',5,'g'],['Kecap manis',15,'ml'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Bebek Goreng Penyet', [['Beras',100,'g'],['Santan',30,'ml'],['Bebek',200,'g'],['Minyak',25,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Bebek Bakar Penyet', [['Beras',100,'g'],['Santan',30,'ml'],['Bebek',200,'g'],['Kecap manis',15,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Bebek Goreng Penyet', [['Beras',100,'g'],['Bebek',200,'g'],['Minyak',25,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Bebek Bakar Penyet', [['Beras',100,'g'],['Bebek',200,'g'],['Kecap manis',15,'ml'],['Sambal penyet',40,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet Bebek Goreng', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Bebek',200,'g'],['Minyak',25,'ml'],['Cabe tanjung',30,'g']]);
        $add('Nasi Liwet Bebek Bakar', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Bebek',200,'g'],['Kecap manis',15,'ml'],['Cabe tanjung',30,'g']]);
        $add('Nasi Tutug Oncom Bebek Goreng', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Bebek',200,'g'],['Minyak',25,'ml'],['Cabe tanjung',30,'g']]);
        $add('Nasi Tutug Oncom Bebek Bakar', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Bebek',200,'g'],['Kecap manis',15,'ml'],['Cabe tanjung',30,'g']]);

        // --- 5. LAUK SATUAN ---
        $add('Jengkol', [['Jengkol',100,'g'],['Bawang merah',8,'g'],['Bawang putih',5,'g'],['Cabe tanjung',10,'g'],['Kecap manis',10,'ml'],['Minyak',10,'ml']]);
        $add('Pete', [['Pete',80,'g'],['Cabe tanjung',10,'g'],['Bawang merah',5,'g'],['Bawang putih',3,'g'],['Minyak',10,'ml']]);
        $add('Ikan Peda', [['Peda',100,'g'],['Bawang merah',5,'g'],['Cabe tanjung',10,'g'],['Tomat',20,'g'],['Minyak',15,'ml']]);
        $add('Ikan Sepat', [['Sepat',100,'g'],['Bawang merah',5,'g'],['Cabe tanjung',10,'g'],['Minyak',15,'ml']]);

        // --- 6. SAYURAN ---
        $add('Kol Goreng', [['Kol',100,'g'],['Bawang putih',5,'g'],['Cabe tanjung',5,'g'],['Garam halus',2,'g'],['Minyak',15,'ml']]);
        $add('Jukut Goreng', [['Kangkung',100,'g'],['Bawang putih',5,'g'],['Bawang merah',5,'g'],['Cabe tanjung',5,'g'],['Minyak',15,'ml']]);
        $add('Karedok', [['Kacang panjang',30,'g'],['Toge',30,'g'],['Kol',30,'g'],['Bonteng',30,'g'],['Kemangi',10,'g'],['Kacang tanah',30,'g'],['Gula kawung',10,'g'],['Cabe tanjung',5,'g'],['Cikur',3,'g']]);
        $add('Lotek', [['Kangkung',40,'g'],['Toge',30,'g'],['Kol',30,'g'],['Kacang panjang',30,'g'],['Kacang tanah',30,'g'],['Gula kawung',10,'g'],['Cabe tanjung',5,'g'],['Cikur',3,'g']]);
        $add('Pencok Kacang', [['Kacang panjang',100,'g'],['Cikur',5,'g'],['Cabe tanjung',5,'g'],['Bawang putih',3,'g'],['Gula kawung',10,'g'],['Garam halus',2,'g']]);

        // --- 7. NASI ---
        $add('Nasi Putih', [['Beras',100,'g']]);
        $add('Nasi Putih Paket', [['Beras',100,'g'],['Tahu',1,'pcs'],['Tempe',50,'g'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Garam halus',2,'g']]);
        $add('Nasi Tutug Oncom', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Cabe tanjung',5,'g']]);
        $add('Nasi Liwet Paket', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Tahu',1,'pcs'],['Tempe',50,'g'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Tutug Oncom Paket', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Tahu',1,'pcs'],['Tempe',50,'g'],['Cabe tanjung',30,'g'],['Kemangi',40,'g']]);
        $add('Nasi Liwet dengan Lalab dan Sambal', [['Beras',100,'g'],['Santan',30,'ml'],['Daun salam',1,'lembar'],['Sereh',3,'g'],['Bonteng',30,'g'],['Kemangi',10,'g'],['Cabe tanjung',30,'g']]);
        $add('Nasi Tutug Oncom dengan Lalab dan Sambal', [['Beras',100,'g'],['Oncom',30,'g'],['Cikur',3,'g'],['Bawang merah',5,'g'],['Bonteng',30,'g'],['Kemangi',10,'g'],['Cabe tanjung',30,'g']]);

        // --- 8. LAUK TAMBAHAN ---
        $add('Ayam Broiler', [['Ayam broiler',150,'g'],['Bawang putih',5,'g'],['Kunyit',3,'g'],['Garam halus',3,'g'],['Minyak',20,'ml']]);
        $add('Ayam Kampung', [['Ayam kampung',180,'g'],['Bawang putih',5,'g'],['Kunyit',3,'g'],['Garam halus',3,'g'],['Minyak',20,'ml']]);
        $add('Bebek', [['Bebek',200,'g'],['Bawang putih',6,'g'],['Kunyit',3,'g'],['Garam halus',3,'g'],['Minyak',25,'ml']]);
        $add('Sambal', [['Cabe tanjung',15,'g'],['Cengek merah',10,'g'],['Tomat',15,'g'],['Bawang merah',5,'g'],['Bawang putih',3,'g'],['Gula putih',3,'g'],['Garam halus',2,'g']]);
        $add('Lalab dan Sambal', [['Bonteng',30,'g'],['Kol',20,'g'],['Kemangi',10,'g'],['Cabe tanjung',30,'g']]);
        $add('Tahu', [['Tahu',1,'pcs'],['Minyak',10,'ml']]);
        $add('Tempe', [['Tempe',50,'g'],['Minyak',10,'ml']]);
        $add('Ikan', [['Ikan nila',150,'g'],['Bawang putih',5,'g'],['Garam halus',3,'g'],['Minyak',20,'ml']]);

        // --- 9. JUS ---
        $add('Jus Sirsak', [['Sirsak',100,'g'],['Gula putih',20,'g'],['Es batu',100,'g']]);
        $add('Jus Mangga', [['Mangga',100,'g'],['Gula putih',20,'g'],['Es batu',100,'g']]);
        $add('Jus Jeruk', [['Jeruk',120,'g'],['Gula putih',15,'g'],['Es batu',100,'g']]);
        $add('Jus Melon', [['Melon',100,'g'],['Gula putih',20,'g'],['Es batu',100,'g']]);
        $add('Jus Jambu', [['Jambu',100,'g'],['Gula putih',20,'g'],['Es batu',100,'g']]);
        $add('Jus Strawberry', [['Strawberry',100,'g'],['Gula putih',20,'g'],['Es batu',100,'g']]);
        $add('Jus Buah Naga', [['Buah naga',100,'g'],['Gula putih',20,'g'],['Es batu',100,'g']]);
        $add('Jus Alpukat', [['Alpukat',120,'g'],['Susu kental manis',30,'ml'],['Gula putih',15,'g'],['Es batu',100,'g']]);

        // --- 10. MINUMAN TRADISIONAL ---
        $add('Bandrek', [['Jahe',20,'g'],['Gula kawung',30,'g'],['Sereh',5,'g']]);
        $add('Bajigur', [['Santan',100,'ml'],['Gula kawung',30,'g'],['Kapal Api',5,'g']]);
        $add('Bandrek Susu', [['Jahe',20,'g'],['Gula kawung',25,'g'],['Sereh',5,'g'],['Susu putih',50,'ml']]);
        $add('Bajigur Susu', [['Santan',80,'ml'],['Gula kawung',25,'g'],['Susu putih',50,'ml'],['Kapal Api',5,'g']]);

        // --- 11. SUSU DAN COKELAT ---
        $add('Susu Putih', [['Susu putih',25,'g'],['Gula putih',10,'g']]);
        $add('Susu Cokelat', [['Susu coklat',25,'g'],['Gula putih',10,'g']]);
        $add('Milo Panas', [['Milo',25,'g'],['Gula putih',10,'g']]);
        $add('Milo Dingin', [['Milo',25,'g'],['Gula putih',10,'g'],['Es batu',100,'g']]);
        $add('Cokelat Panas', [['Beng-Beng Drink',25,'g'],['Gula putih',10,'g']]);
        $add('Cokelat Dingin', [['Beng-Beng Drink',25,'g'],['Gula putih',10,'g'],['Es batu',100,'g']]);

        // --- 12. KOPI ---
        $add('Es Cappuccino', [['Cappuccino',25,'g'],['Gula putih',10,'g'],['Es batu',100,'g']]);
        $add('Es Creamy Latte', [['Creamy Latte',25,'g'],['Gula putih',10,'g'],['Es batu',100,'g']]);
        $add('Kopi Kapal Api', [['Kapal Api',15,'g'],['Gula putih',15,'g']]);
        $add('Kopi Good Day', [['Good Day',1,'sachet']]);
        $add('Kopi Luwak', [['Luwak',1,'sachet']]);
        $add('Kopi Indocafe', [['Indocafe',1,'sachet']]);
        $add('Kopi ABC Susu', [['ABC Susu',1,'sachet']]);
        $add('Cappuccino', [['Cappuccino',25,'g'],['Gula putih',10,'g']]);
        $add('Creamy Latte', [['Creamy Latte',25,'g'],['Gula putih',10,'g']]);

        // --- 13. AIR MINERAL ---
        $add('Air Mineral', [['Air mineral kemasan',1,'botol']]);

        $this->command->info('Selesai import resep menu Dine-In! Total menu: '.count($menuMap));
    }
}
