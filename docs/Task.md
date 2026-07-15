Task List — Form Publik Catering & Nasi Box (v2)

BBC Resto — Lanjutan setelah menu selesai diinput

Tanggal: 13 Juli 2026
Status menu: ✅ Sudah diinput (varian nasi box + menu isi komponen catering)
Yang di-skip: Semua task seed/input menu (sudah selesai)

⚠️ Cek dulu sebelum mulai coding

#CekStatus1BOM tiap varian nasi box sudah diisi?Cek di tabel menu_ingredients2BOM tiap menu isi komponen catering (Rendang, Sup Kimlo, dst) sudah diisi?Cek di tabel menu_ingredients3Flag/kategori "Nasi Box" & "Catering Only" sudah ada di tabel menus?Pastikan tidak muncul di POS4Tabel paket_catering, komponen_paket, opsi_komponen sudah ada & ter-seed?Paket A (9 komponen) & Paket B5Kebijakan pembatalan nasi box sudah diputuskan?Dibutuhkan di task E-2

Jangan lanjut ke bagian A jika poin 1–4 belum ✅ — backend logic bergantung ke data ini.

BAGIAN A — Database & Model

A-1 — Migrasi tabel pesanan

Buat migrasi tabel pesanan_catering:

id, kode_pesanan (unique, auto-generate),
nama_pemesan, kontak, lokasi_acara,
paket_id (FK), jumlah_porsi,
total_tagihan, dp_amount,
status (enum: menunggu_dp, menunggu_konfirmasi,
terkonfirmasi, lunas, dibatalkan),
catatan, created_at, updated_at

Buat migrasi tabel pesanan_catering_detail:

id, pesanan_id (FK), komponen_id (FK), menu_id_terpilih (FK ke menus)

Buat migrasi tabel pesanan_catering_addon:

id, pesanan_id (FK), layanan_tambahan_id (FK), catatan

Buat migrasi tabel layanan_tambahan:

id, nama, harga

Seed tabel layanan_tambahan:
Peralatan Prasmanan, Dekorasi, Pramusaji, Pengantaran (isi harga sesuai kebijakan)
Buat migrasi tabel pesanan_nasi_box:

id, kode_pesanan (unique, auto-generate),
nama_pemesan, kontak, alamat,
menu_id (FK ke menus — varian yang dipilih), jumlah_box,
total_tagihan, dp_amount,
status (enum sama seperti catering),
catatan, created_at, updated_at

Buat migrasi tabel bukti_pembayaran:

id, pesanan_type (catering/nasi_box), pesanan_id,
jenis_pembayaran (dp/pelunasan),
file_path, status (menunggu_verifikasi/verified/ditolak),
catatan_admin, created_at

A-2 — Model & Relasi (Eloquent)

Model PesananCatering:

hasMany PesananCateringDetail
hasMany PesananCateringAddon
hasMany BuktiPembayaran (morphMany)
belongsTo PaketCatering
Method: generateKodePesanan(), hitungDP()

Model PesananCateringDetail:

belongsTo KomponenPaket
belongsTo Menu (menu_id_terpilih)

Model PesananCateringAddon:

belongsTo LayananTambahan

Model PesananNasiBox:

belongsTo Menu
hasMany BuktiPembayaran (morphMany)
Method: generateKodePesanan(), hitungDP()

Model LayananTambahan
Model BuktiPembayaran (polymorphic — dipakai oleh catering & nasi box)

BAGIAN B — Backend: Validasi & Helper

B-1 — FormRequest: StorePesananCateringRequest

tanggal_acara ≥ Carbon::today()->addDays(14) — tolak jika kurang
jumlah_porsi required, integer, min:1
paket_id required, exists di tabel paket_catering
Untuk tiap komponen choice di paket yang dipilih: wajib ada pilihan menu yang valid
nama_pemesan, kontak required
layanan_tambahan optional, array, tiap item exists di tabel layanan_tambahan
Custom error message dalam Bahasa Indonesia

B-2 — FormRequest: StorePesananNasiBoxRequest

tanggal_acara ≥ Carbon::today()->addDays(2) — minimal 2 hari dari sekarang
jumlah_box required, integer, min:10
menu_id required, exists di tabel menus, kategori = "Nasi Box"
nama_pemesan, kontak required
Custom error message dalam Bahasa Indonesia

B-3 — Helper: Hitung total & DP catering

php// PesananCateringService::hitungTotal($paketId, $jumlahPorsi, $layananTambahanIds)
// Return: ['subtotal_menu' => X, 'subtotal_addon' => Y, 'total' => Z, 'dp' => W]

Ambil harga_per_porsi dari paket_catering
Subtotal menu = harga_per_porsi × jumlah_porsi
Subtotal addon = SUM harga layanan_tambahan yang dipilih
Total = subtotal_menu + subtotal_addon
DP = ceil(total × 0.5) — bulatkan ke atas

B-4 — Helper: Hitung total & DP nasi box

php// PesananNasiBoxService::hitungTotal($menuId, $jumlahBox)
// Return: ['harga_per_box' => X, 'total' => Y, 'dp' => Z]

Ambil harga dari menus
Total = harga × jumlah_box
DP = ceil(total × 0.25)

B-5 — Helper: Potong stok saat konfirmasi catering

php// PesananCateringService::potongStok($pesananId)
// Return: true | array bahan yang kurang

Loop tiap pesanan_catering_detail → ambil menu_id_terpilih
Ambil BOM menu tersebut dari menu_ingredients
Hitung kebutuhan = qty_bom × jumlah_porsi pesanan
Tambah kebutuhan komponen fixed (nasi putih, kerupuk, air mineral) × jumlah_porsi
Bandingkan total kebutuhan vs stok saat ini
Jika ada yang kurang → return array kekurangan (jangan potong dulu)
Jika semua cukup → kurangi stok, catat mutasi stok, return true

B-6 — Helper: Potong stok saat konfirmasi nasi box

php// PesananNasiBoxService::potongStok($pesananId)
// Return: true | array bahan yang kurang

Ambil menu_id dari pesanan
Ambil BOM dari menu_ingredients
Kebutuhan = qty_bom × jumlah_box
Cek stok → potong atau return kekurangan

B-7 — Controller: PesananCateringController (publik)

index() — GET /pesan/catering → load view form + data paket
getKomponen($paketId) — GET /pesan/catering/komponen/{paketId} → return JSON komponen + opsi (untuk dynamic load)
 preview(Request) — POST /pesan/catering/preview → return JSON total tagihan (dipanggil saat form berubah, tanpa simpan ke DB)
 store(StorePesananCateringRequest) → simpan pesanan + detail + addon, generate kode pesanan, redirect ke halaman DP
 cekStatus($kodePesanan) — GET /pesan/status/{kode} → tampilkan status pesanan (publik)

B-8 — Controller: PesananNasiBoxController (publik)

index() — GET /pesan/nasi-box → load view + data varian nasi box
preview(Request) — POST /pesan/nasi-box/preview → return JSON total + DP
store(StorePesananNasiBoxRequest) → simpan pesanan, redirect ke halaman DP
(Cek status pakai controller yang sama dengan catering via route /pesan/status/{kode})

B-9 — Controller: BuktiPembayaranController (publik)

store(Request) — POST /pesan/bukti → upload file, simpan ke storage, update status pesanan ke menunggu_konfirmasi
Validasi file: mimes jpeg,png,pdf, max 2MB
Simpan path file ke tabel bukti_pembayaran

BAGIAN C — Frontend: Form Catering

C-1 — Layout & struktur halaman /pesan/catering

Navbar publik (logo + link ke menu & nasi box)
Section 1: pilih paket (card Paket A vs B, tampilkan harga & deskripsi singkat)
Section 2: komponen (muncul setelah paket dipilih)
Section 3: detail acara
Section 4: layanan tambahan
Section 5: ringkasan + total + tombol lanjut
Mobile-responsive (single column di mobile)

C-2 — Pilih paket (Section 1)

2 card: Paket A & Paket B
Tampilkan: nama paket, harga per porsi, daftar komponen fixed sebagai preview
Klik card → selected state (border highlight), trigger load komponen via fetch/AJAX

C-3 — Komponen dinamis (Section 2)

Fetch ke /pesan/catering/komponen/{paketId} saat paket dipilih
Render tiap komponen:

fixed → tampil sebagai badge/chip (tidak bisa diklik): "Nasi putih ✓"
choice → tampil sebagai radio group dengan label nama komponen

Urutan sesuai field urutan
Validasi: semua choice harus dipilih sebelum bisa lanjut

C-4 — Detail acara (Section 3)

Input tanggal acara (date, min = hari ini + 14)
Saat tanggal < H-14: border merah + pesan error "Pemesanan catering minimal H-14 sebelum acara"
Input jumlah porsi (number, min 1)
Input nama pemesan, nomor kontak, lokasi acara

C-5 — Layanan tambahan (Section 4)

Checkbox list dengan harga tiap item
Saat dicentang/uncentang → panggil preview endpoint → update subtotal addon & total di Section 5

C-6 — Ringkasan real-time (Section 5)

Tampilkan:

Subtotal menu : Rp X (harga_per_porsi × jumlah_porsi)
Layanan tambahan : Rp Y
Total tagihan : Rp Z
DP yang dibayar : Rp W (50% dari total)

Update otomatis saat jumlah porsi atau layanan tambahan berubah
Tombol "Lanjut ke Pembayaran" — disabled jika ada validasi yang belum terpenuhi
Saat tombol diklik: POST form ke /pesan/catering

BAGIAN D — Frontend: Form Nasi Box

D-1 — Layout halaman /pesan/nasi-box

Info minimal order 10 box di bagian atas (banner/alert)
Grid varian: Paket A / B / C sebagai card
Tiap card: nama varian, harga per box, isi paket (list item)
Mobile-responsive

D-2 — Pilih varian

Klik card → selected state
Tampilkan harga per box yang terpilih di section ringkasan

D-3 — Detail pesanan

Input jumlah box (number, min 10)
Warning real-time jika < 10: "Minimal order 10 box"
Input tanggal acara (date, min = hari ini + 2)
Warning jika tanggal < H+2: "Pesanan nasi box maksimal H-2 sebelum acara"
Input nama pemesan, nomor kontak

D-4 — Ringkasan real-time

Tampilkan:

Harga per box : Rp X
Jumlah box : N
Total tagihan : Rp Y
DP yang dibayar: Rp Z (25% dari total)

Update saat jumlah box berubah (perkalian di JS, tidak perlu hit server)
Tombol "Lanjut ke Pembayaran"

BAGIAN E — Halaman Pembayaran DP

E-1 — Halaman instruksi DP (shared catering & nasi box)

URL: /pesan/bayar/{kodePesanan}
Tampilkan ringkasan pesanan + nominal DP
Kode unik pesanan yang harus dicantumkan di keterangan transfer
Pilihan metode: Transfer Bank / QRIS (tab atau radio)
Jika Transfer Bank: tampilkan nomor rekening + nama bank + nama penerima
Jika QRIS: placeholder dulu (integrasi payment gateway menyusul — Task 8)

E-2 — Upload bukti transfer

Form upload (jpg/png/pdf, max 2MB)
Preview file sebelum submit
POST ke /pesan/bukti
Setelah berhasil: tampilkan halaman sukses + kode pesanan untuk cek status

E-3 — Halaman cek status pesanan (/pesan/status/{kodePesanan})

Input kode pesanan (jika akses langsung tanpa redirect)
Tampilkan: status pesanan, tanggal acara, nominal DP, nominal pelunasan
Badge status berwarna sesuai: kuning (menunggu), biru (menunggu konfirmasi), hijau (terkonfirmasi/lunas)
Jika status terkonfirmasi: tampilkan countdown H-3 pelunasan
Jika status menunggu_konfirmasi: tampilkan "Sedang diverifikasi oleh admin"

BAGIAN F — Sisi Admin (Pemilik)

F-1 — Halaman daftar pesanan catering

Route: /admin/pesanan/catering (auth: Pemilik)
Tabel: kode, nama pemesan, paket, jumlah porsi, tanggal acara, total, status
Filter: by status, by range tanggal acara
Baris mendekati H-3 → highlight/warning otomatis
Klik baris → ke halaman detail (F-2)

F-2 — Detail & aksi pesanan catering

Tampilkan semua komponen + pilihan menu konsumen
Layanan tambahan yang dipilih
Preview bukti pembayaran DP
Tombol "Verifikasi DP" → update status bukti ke verified, update pesanan ke menunggu_konfirmasi
Tombol "Konfirmasi Pesanan" (muncul setelah DP verified):

Panggil PesananCateringService::potongStok()
Jika return array kekurangan → tampilkan modal: tabel bahan yang kurang + jumlah kekurangan + tombol "Ke Halaman Pengadaan"
Jika return true → update status ke terkonfirmasi, tampilkan success

F-3 — Halaman daftar pesanan nasi box

Route: /admin/pesanan/nasi-box (auth: Pemilik)
Sama seperti F-1 tapi untuk nasi box

F-4 — Detail & aksi pesanan nasi box

Sama seperti F-2 tapi potong stok menggunakan PesananNasiBoxService::potongStok()

F-5 — Notifikasi dashboard H-3 (extend yang sudah ada)

Pastikan notifikasi yang sudah ada di dashboard cover nasi box juga (bukan cuma catering)
Cek query: WHERE tanggal_acara <= today + 3 AND status IN ('menunggu_konfirmasi') — berlaku untuk kedua tabel

BAGIAN G — Testing

G-1 — Validasi form

Catering: tanggal < H-14 → ditolak di frontend + backend
Nasi box: jumlah < 10 → ditolak
Nasi box: tanggal < H+2 → ditolak
Catering: ada komponen choice yang belum dipilih → ditolak

G-2 — Kalkulasi total

Paket A, 100 porsi, + 1 layanan tambahan → cek subtotal menu, addon, total, DP 50%
Nasi Box B, 25 box → cek total, DP 25%
Pastikan format Rupiah tampil benar di semua angka

G-3 — Alur submit → DP → konfirmasi → potong stok

Catering: submit form → redirect ke halaman DP → upload bukti → Pemilik verifikasi → Pemilik konfirmasi → stok terpotong sesuai BOM komponen yang dipilih × porsi
Nasi box: submit → DP → konfirmasi → stok terpotong sesuai BOM varian × jumlah box
Coba konfirmasi saat stok kurang → warning muncul, stok tidak terpotong

G-4 — Status flow

Pesanan tidak bisa loncat status (misal konfirmasi tanpa DP terverifikasi → harus ditolak)
Cek status via /pesan/status/{kode} dari sisi konsumen

Urutan Pengerjaan

A-1 → A-2 database & model dulu
B-1 → B-9 semua backend logic & controller
C-1 → C-6 frontend form catering
D-1 → D-4 frontend form nasi box (lebih cepat)
E-1 → E-3 halaman DP & cek status
F-1 → F-5 sisi admin
G-1 → G-4 testing
