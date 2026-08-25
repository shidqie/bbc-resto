<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\PilihanItemPaket;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Menu images
        $menuImageMap = [
            232 => 'images/Nasi_Ayam/Nasi_Ayam_Goreng.webp',
            233 => 'images/Nasi_Ayam/Nasi_Ayam_Bakar.webp',
            234 => 'images/Lauk_Tambahan/Ikan.webp',
            235 => 'images/Lauk_Tambahan/Ikan.webp',
            236 => 'images/Katering/SambalGorengKetang.webp',
            237 => 'images/Katering/SambalGorengKetang.webp',
            238 => 'images/Lauk_Tambahan/Lalab_Sambal.webp',
            239 => 'images/Katering/KerupukUdang.webp',
            240 => 'images/Jus/Jus_Melon.webp',
            241 => 'images/Katering/BuahPotong.webp',
            242 => 'images/Jus/Jus_Jeruk.webp',
            243 => 'images/Katering/BuahPotong.webp',
            244 => 'images/Lauk_Tambahan/Tempe.webp',
            245 => 'images/Lauk_Tambahan/Tahu.webp',
            246 => 'images/Sayuran/Jukut_Goreng.webp',
            247 => 'images/Sayuran/Kol_Goreng.webp',
            248 => 'images/Sayuran/Lotek.webp',
            271 => 'images/Nasi_Ayam_Kampung/Nasi_Ayam_Kampung_Bakar.webp',
            272 => 'images/Nasi_Ayam_Kampung/Nasi_Ayam_Kampung_Goreng.webp',
            273 => 'images/Nasi_Bebek/Nasi_Bebek_Bakar.webp',
            274 => 'images/Nasi_Bebek/Nasi_Bebek_Goreng.webp',
            275 => 'images/Lauk_Tambahan/Ikan.webp',
            276 => 'images/Lauk_Satuan/Ikan_Peda.webp',
            277 => 'images/Sayuran/Lotek.webp',
            278 => 'images/Lauk_Tambahan/Lalab_Sambal.webp',
        ];

        foreach ($menuImageMap as $id => $img) {
            DB::table('menu')->where('id', $id)->update(['foto' => $img]);
        }

        // 2. Link PilihanItemPaket to Menu and set foto
        $pilihans = PilihanItemPaket::all();
        foreach ($pilihans as $p) {
            $matched = Menu::where('nama_menu', $p->nama_pilihan)->first();
            if ($matched) {
                $p->menu_id = $matched->id;
                $p->foto = $matched->foto;
                $p->save();
            }
        }

        // 3. Update specific Catering pilihan foto
        $cateringFotoMap = [
            'Sup Kimlo' => 'images/Katering/SupKimlo.webp',
            'Sup Bakso' => 'images/Katering/SupBakso.webp',
            'Sup Ayam Sosis' => 'images/Katering/SupSosis.webp',
            'Sup Sosis' => 'images/Katering/SupSosis.webp',
            'Sapi Teriyaki' => 'images/Katering/SapiTeriyaki.webp',
            'Rendang' => 'images/Katering/Rendang.webp',
            'Bistik' => 'images/Katering/Bistik.webp',
            'Dori Asam Manis' => 'images/Katering/IkanDoriAsamManis.webp',
            'Dori Saus Mentega' => 'images/Katering/DoriSausMentega.webp',
            'Sambal Goreng Ati Kentang' => 'images/Katering/SambalGorengKetang.webp',
            'Salad Buah' => 'images/Katering/SaladBuah.webp',
            'Salad Sayuran' => 'images/Katering/saladSayur.webp',
            'Gado-Gado' => 'images/Katering/GadoGado.webp',
            'Rujak Buah' => 'images/Katering/Rujak.webp',
            'Kerupuk Udang' => 'images/Katering/KerupukUdang.webp',
            'Air Mineral' => 'images/Katering/Air.webp',
            'Bakso Tahu' => 'images/Katering/BaksoTahu.webp',
            'Mi Kocok' => 'images/Katering/MiKocok.webp',
            'Buah Potong' => 'images/Katering/BuahPotong.webp',
            'Es Krim' => 'images/Katering/EsKrim.webp',
            'Ayam Teriyaki' => 'images/Katering/AyamTeriyaki.webp',
            'Ayam Suwir' => 'images/Katering/AyamSuwir.webp',
            'Ayam Rica-Rica' => 'images/Katering/AyamRicaRica.webp',
        ];

        foreach ($cateringFotoMap as $nama => $img) {
            DB::table('pilihan_item_paket')->where('nama_pilihan', $nama)->update(['foto' => $img]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback
    }
};
