# Bugfix Requirements Document

## Introduction

Kolom "Meja" tidak seharusnya ditampilkan di halaman daftar pesanan karena informasi meja tidak relevan atau tidak digunakan dalam sistem ini. Kolom ini muncul di tabel daftar pesanan baik di halaman admin maupun POS, menyebabkan tampilan yang membingungkan dan tidak konsisten dengan kebutuhan bisnis.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN pengguna membuka halaman daftar pesanan admin (`/admin/pesanan`) THEN sistem menampilkan kolom "Meja" di header tabel dan body tabel

1.2 WHEN pengguna membuka halaman daftar pesanan POS (`/pos/pesanan`) THEN sistem menampilkan kolom "Meja" di header tabel dan body tabel

### Expected Behavior (Correct)

2.1 WHEN pengguna membuka halaman daftar pesanan admin (`/admin/pesanan`) THEN sistem SHALL NOT menampilkan kolom "Meja" di header tabel maupun body tabel

2.2 WHEN pengguna membuka halaman daftar pesanan POS (`/pos/pesanan`) THEN sistem SHALL NOT menampilkan kolom "Meja" di header tabel maupun body tabel

### Unchanged Behavior (Regression Prevention)

3.1 WHEN pengguna membuka halaman daftar pesanan admin THEN sistem SHALL CONTINUE TO menampilkan semua kolom lain (No, Tanggal, Pelanggan, Total, Status, dll) dengan format yang sama

3.2 WHEN pengguna membuka halaman daftar pesanan POS THEN sistem SHALL CONTINUE TO menampilkan semua kolom lain (No, Tanggal, Pelanggan, Total, Status, dll) dengan format yang sama

3.3 WHEN pengguna melakukan aksi pada baris pesanan (view, edit, delete) THEN sistem SHALL CONTINUE TO berfungsi dengan normal tanpa error

3.4 WHEN data pesanan dimuat dari database THEN sistem SHALL CONTINUE TO mengambil dan menampilkan data dengan performa yang sama
