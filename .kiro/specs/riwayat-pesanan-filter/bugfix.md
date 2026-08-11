# Bugfix Requirements Document

## Introduction

Di halaman Detail Konsumen, bagian "Riwayat Pesanan" seharusnya hanya menampilkan pesanan yang sudah selesai atau lunas. Saat ini, sistem menampilkan SEMUA pesanan termasuk yang masih dalam proses (pending, diproses, dll), yang menyebabkan kebingungan karena "riwayat" seharusnya merujuk pada transaksi yang sudah completed, bukan transaksi yang masih berlangsung.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN pengguna membuka halaman Detail Konsumen (`/users/show-pelanggan/{id}`) THEN sistem menampilkan SEMUA pesanan pelanggan di bagian "Riwayat Pesanan" termasuk pesanan dengan status pending, diproses, atau status lain yang belum selesai

1.2 WHEN pesanan pelanggan memiliki berbagai status (pending, diproses, selesai, batal) THEN sistem menampilkan semuanya tanpa filter di bagian "Riwayat Pesanan"

### Expected Behavior (Correct)

2.1 WHEN pengguna membuka halaman Detail Konsumen (`/users/show-pelanggan/{id}`) THEN sistem SHALL menampilkan hanya pesanan dengan status "selesai" di bagian "Riwayat Pesanan"

2.2 WHEN pesanan pelanggan memiliki berbagai status (pending, diproses, selesai, batal) THEN sistem SHALL memfilter dan hanya menampilkan pesanan dengan `status_pesanan.kode_status = 'selesai'` di bagian "Riwayat Pesanan"

### Unchanged Behavior (Regression Prevention)

3.1 WHEN sistem mengambil riwayat pesanan THEN sistem SHALL CONTINUE TO mengurutkan pesanan berdasarkan tanggal terbaru (latest)

3.2 WHEN sistem mengambil riwayat pesanan THEN sistem SHALL CONTINUE TO membatasi hasil menjadi 10 pesanan terakhir (take 10)

3.3 WHEN pesanan ditampilkan di bagian "Riwayat Pesanan" THEN sistem SHALL CONTINUE TO menampilkan semua informasi pesanan (nomor, tanggal, items, total, dll) dengan format yang sama

3.4 WHEN pelanggan tidak memiliki pesanan dengan status "selesai" THEN sistem SHALL CONTINUE TO menampilkan daftar kosong atau pesan yang sesuai tanpa error

3.5 WHEN relasi antara pelanggan dan pesanan dimuat THEN sistem SHALL CONTINUE TO menggunakan eager loading atau query yang efisien untuk menghindari N+1 problem
