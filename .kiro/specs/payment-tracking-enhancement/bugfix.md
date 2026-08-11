# Bugfix Requirements Document

## Introduction

The admin payment tracking page (`admin/pembayaran/index`) currently displays individual payment records (Pembayaran) in a flat list, showing each payment transaction separately. This makes it difficult for administrators to track the overall payment status of Catering and Nasi Box orders, which require multiple payments (DP and Pelunasan). The page needs to be redesigned to display order-level payment tracking with comprehensive information about total billing, amounts paid, remaining balance, and overall payment status.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN viewing the admin payment page THEN the system displays individual payment records (Pembayaran) instead of order-level payment summaries

1.2 WHEN an order has multiple payments (DP and Pelunasan) THEN the system shows separate rows for each payment, making it difficult to see the overall payment status

1.3 WHEN viewing payment information THEN the system only displays `jumlah_bayar` (individual payment amount) and does not show total billing, total paid, or remaining balance

1.4 WHEN viewing the Bukti Pembayaran column THEN the system only shows a link to individual payment proof without indicating whether it's DP or Pelunasan

1.5 WHEN viewing the Status column THEN the system only shows individual payment verification status (menunggu_verifikasi, diterima, ditolak) without indicating overall order payment status

1.6 WHEN viewing the Aksi column for verified payments THEN the system only provides a Detail button, without access to view payment proof or generate invoices for completed orders

1.7 WHEN an order requires pelunasan (remaining payment) THEN the system does not clearly indicate how much is still owed or provide easy access to pelunasan payment proof

1.8 WHEN customers need to upload payment proof THEN there is no mention or indication of where customers can upload DP or Pelunasan proof

### Expected Behavior (Correct)

2.1 WHEN viewing the admin payment page for Catering and Nasi Box orders THEN the system SHALL display order-level payment summaries (one row per order) with columns: No, Kode Pesanan, Tanggal Pesan, Tanggal Acara, Total Tagihan, Total Dibayar, Sisa Tagihan, Bukti Pembayaran, Status Pembayaran, Aksi

2.2 WHEN an order has multiple payments (DP and Pelunasan) THEN the system SHALL aggregate payment information to show the complete payment tracking in a single row

2.3 WHEN viewing the Total Tagihan column THEN the system SHALL display `pesanan.total_tagihan` formatted as currency

2.4 WHEN viewing the Total Dibayar column THEN the system SHALL display the sum of all verified payment amounts (`SUM(pembayaran.jumlah_bayar WHERE status_verifikasi = 'diterima')`) formatted as currency

2.5 WHEN viewing the Sisa Tagihan column THEN the system SHALL display the remaining balance calculated as `total_tagihan - total_dibayar` formatted as currency

2.6 WHEN viewing the Bukti Pembayaran column THEN the system SHALL display links indicating payment type: "DP: Lihat Bukti" for down payment only, or "DP & Pelunasan: Lihat" when both payments exist

2.7 WHEN viewing the Status Pembayaran column THEN the system SHALL display overall order payment status based on payment state:
   - "Menunggu Verifikasi DP" when DP uploaded but status_verifikasi = 'menunggu_verifikasi'
   - "Menunggu Pelunasan" when DP verified (status_verifikasi = 'diterima') but pelunasan not yet uploaded or verified
   - "Menunggu Verifikasi Pelunasan" when pelunasan uploaded but status_verifikasi = 'menunggu_verifikasi'
   - "Lunas" when both DP and pelunasan verified (status_verifikasi = 'diterima') and sisa_tagihan = 0

2.8 WHEN viewing the Aksi column for orders with status "Menunggu Verifikasi DP" or "Menunggu Verifikasi Pelunasan" THEN the system SHALL provide buttons: "Detail", "Verifikasi", "Tolak"

2.9 WHEN viewing the Aksi column for orders with status "Menunggu Pelunasan" THEN the system SHALL provide buttons: "Detail", "Lihat Bukti"

2.10 WHEN viewing the Aksi column for orders with status "Lunas" THEN the system SHALL provide buttons: "Detail", "Invoice"

2.11 WHEN customers create a Catering or Nasi Box order THEN the system SHALL allow them to upload DP payment proof (bukti_pembayaran) through the customer interface

2.12 WHEN customers have DP verified THEN the system SHALL allow them to upload Pelunasan payment proof (bukti_pembayaran) through the customer interface

### Unchanged Behavior (Regression Prevention)

3.1 WHEN viewing Dine-in payment records THEN the system SHALL CONTINUE TO display them in the current format (single payment, immediate verification flow)

3.2 WHEN filtering payment records using the search function THEN the system SHALL CONTINUE TO filter by payment ID or order code (nomor_pesanan)

3.3 WHEN verifying a payment (clicking Verifikasi button) THEN the system SHALL CONTINUE TO update status_verifikasi to 'diterima', set tanggal_verifikasi, and trigger stock deduction if applicable

3.4 WHEN canceling/rejecting a payment (clicking Batal/Tolak button) THEN the system SHALL CONTINUE TO update status_verifikasi to 'ditolak' and record the reason

3.5 WHEN viewing payment detail (clicking Detail button) THEN the system SHALL CONTINUE TO load payment details in the drawer using AJAX

3.6 WHEN viewing payment proof (clicking Lihat Bukti) THEN the system SHALL CONTINUE TO open the proof image in a new tab using Storage::url()

3.7 WHEN pagination displays payment records THEN the system SHALL CONTINUE TO show row numbers based on paginated position using `($pembayarans->firstItem() ?? 1) + $index`

3.8 WHEN the payment table loads THEN the system SHALL CONTINUE TO use the responsive data-table component with integrated toolbar for search functionality
