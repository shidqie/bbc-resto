@extends('layouts.pos')

@php $isPelayan = false; @endphp

@section('content')
{{-- ╔══════════════════════════════════════╗ --}}
{{-- ║  BBC RESTO — POINT OF SALE (POS)     ║ --}}
{{-- ╚══════════════════════════════════════╝ --}}
<style>
  .pos-root        { font-family: 'Outfit', sans-serif; }
  .chip-active     { background:#0D3024; color:#ffffff; border: 1px solid #0D3024; }
  .chip-default    { background:#FFFFFF; color:#374151; border: 1px solid #E5E7EB; }
  .chip-default:hover { background:#F9FAFB; color:#111827; border-color:#D1D5DB; }
  .card-menu:hover { border-color:rgba(15,46,35,.35); box-shadow:0 8px 24px rgba(15,46,35,.08); transform: translateY(-2px); }
  .mono            { font-family:'Outfit', sans-serif; letter-spacing:.02em; }

  @media print {
    body * {
      visibility: hidden !important;
    }
    #printableCheckerArea, #printableCheckerArea * {
      visibility: visible !important;
    }
    #printableCheckerArea {
      position: fixed !important;
      left: 0 !important;
      top: 0 !important;
      width: 270px !important;
      max-width: 270px !important;
      margin: 0 auto !important;
      padding: 0 !important;
      border: none !important;
      box-shadow: none !important;
      background: #ffffff !important;
      color: #000000 !important;
    }
    .no-print {
      display: none !important;
    }
  }
</style>
<script>
function posSystemData() {
  return {
    // View state
    leftView: '{{ $isPelayan ? "open_bills" : (in_array(request("view"), ["open_bills", "pesanan_belum_dibayar"]) ? "open_bills" : (request("view", "menu"))) }}',
    activeCategory: 'semua',
    searchQuery: '',
    tableSearch: '',
    tableFilter: 'semua',
    mejaLayoutMode: 'grid',
    openBillSearch: '',
    openBillFilter: 'semua',
    openBillStatusFilter: 'semua',
    riwayatDateQuick: 'today',
    riwayatStartDate: '',
    riwayatEndDate: '',
    riwayatKasirFilter: 'semua',
    riwayatStatusFilter: 'semua',
    riwayatPayFilter: 'semua',
    riwayatSearch: '',
    riwayatTransaksi: @json($riwayatTransaksi ?? []),
    mejasList: @json($mejas ?? []),
    showTableDropdown: false,

    // Real-time table status polling
    tableStatusPolling: null,
    mejaStatusMap: {}, // meja_id -> { has_active_order, active_order, kot }

    // Modals Detail & Void & Cetak Struk
    selectedTrxDetail: null,
    showTrxDetailModal: false,
    showReceiptPreview: false,
    receiptType: 'pelanggan',
    showKitchenPreview: false,
    previewTrx: null,
    activeDropdown: null,
    showCheckerModal: false,
    checkerBill: null,
    showCheckerPreviewModal: false,
    checkerPreviewBill: null,
    showVoidModal: false,
    trxToVoid: null,
    alasanVoidInput: 'Salah Input Menu',
    catatanVoidInput: '',
    showCetakStrukModal: false,
    showSavePrintModal: false,
    showSuccessModal: false,
    cetakStrukTargetId: null,
    pendingCheckoutAction: false,
    checkoutTargetMeja: null,
    selectedPrintOptions: ['dapur', 'meja'],
    togglePrintOption(opt) {
      if (this.selectedPrintOptions.includes(opt)) {
        this.selectedPrintOptions = this.selectedPrintOptions.filter(o => o !== opt);
      } else {
        this.selectedPrintOptions.push(opt);
      }
    },
    closePrintModal() {
      this.showSavePrintModal = false;
      this.showCetakStrukModal = false;
      if (this.pendingCheckoutAction && this.checkoutTargetMeja) {
          window.location.href = `/pos/dinein/meja/${this.checkoutTargetMeja}/checkout`;
      }
    },
    openCetakStrukModal(id, action = 'simpan', mejaId = null) {
      this.savedPesananId = id;
      this.cetakStrukTargetId = id;
      this.pendingCheckoutAction = (action === 'bayar');
      this.checkoutTargetMeja = mejaId;
      this.selectedPrintOptions = action === 'simpan' ? ['meja', 'dapur'] : ['meja', 'dapur', 'konsumen'];
      this.showSavePrintModal = true;
      this.showCetakStrukModal = true;
    },
    async executePrintSelection() {
      if (!this.selectedPrintOptions.length) {
        return Swal.fire({ icon: 'warning', title: 'Pilih Struk', text: 'Pilih minimal 1 jenis struk yang akan dicetak!', confirmButtonColor: '#0D3024' });
      }
      
      const targetId = this.savedPesananId || this.cetakStrukTargetId;
      if (!targetId) return;

      const hasMeja = this.selectedPrintOptions.includes('meja');
      const hasDapur = this.selectedPrintOptions.includes('dapur');
      const hasKonsumen = this.selectedPrintOptions.includes('konsumen');

      if (hasMeja && hasDapur) {
        this.printSilentIframe('/pos/dinein/pesanan/' + targetId + '/print-gabungan');
      } else if (hasMeja) {
        this.printSilentIframe('/pos/dinein/pesanan/' + targetId + '/print-meja');
      } else if (hasDapur) {
        this.printSilentIframe('/pos/dinein/pesanan/' + targetId + '/print-dapur');
      }

      if (hasKonsumen) {
        setTimeout(() => {
          this.printSilentIframe('/pos/dinein/pesanan/' + targetId + '/print-nota');
        }, (hasMeja || hasDapur) ? 800 : 0);
      }

      this.closePrintModal();
    },

    openDetailModal(trx) {
      if (!trx) return;
      let target = trx;
      const billsArr = Array.isArray(this.openBills) ? this.openBills : Object.values(this.openBills || {});
      if ((!target.items || target.items.length === 0) && target.id && billsArr.length > 0) {
        const found = billsArr.find(b => b.id === target.id);
        if (found) target = found;
      }
      if ((!target.items || target.items.length === 0) && target.detail_pesanan) {
        target.items = target.detail_pesanan.map(d => ({
          id: d.id,
          qty: d.jumlah || d.qty || 1,
          harga_satuan: d.harga_satuan || (d.menu ? (d.menu.harga_jual || d.menu.harga || 0) : 0),
          subtotal: d.subtotal || ((d.harga_satuan || (d.menu ? (d.menu.harga_jual || d.menu.harga || 0) : 0)) * (d.jumlah || d.qty || 1)),
          catatan: d.catatan,
          is_tambahan: !!d.is_tambahan,
          batch_pesanan: d.batch_pesanan || 1,
          menu: d.menu ? {
            id: d.menu.id,
            nama: d.menu.nama_menu || d.menu.nama || 'Menu',
            harga: d.menu.harga_jual || d.menu.harga || 0
          } : null
        }));
      }
      this.selectedTrxDetail = target;
      this.showTrxDetailModal = true;
    },

    openReceiptPreview(trx, type = 'pelanggan') {
      this.previewTrx = trx;
      this.receiptType = type;
      this.showReceiptPreview = true;
    },

    openKitchenPreview(trx) {
      this.previewTrx = trx;
      this.showKitchenPreview = true;
    },

    openCheckerPreview(bill) {
      if (!bill) return;
      this.showSuccessModal = false;
      this.showCheckerModal = false;
      this.showReceiptPreview = false;
      this.showKitchenPreview = false;

      let targetBill = bill;
      const billsArr = Array.isArray(this.openBills) ? this.openBills : Object.values(this.openBills || {});
      if ((!targetBill.items || targetBill.items.length === 0) && targetBill.id && billsArr.length > 0) {
        const found = billsArr.find(b => b.id === targetBill.id);
        if (found) targetBill = found;
      }
      if ((!targetBill.items || targetBill.items.length === 0) && targetBill.detail_pesanan) {
        targetBill.items = targetBill.detail_pesanan.map(d => ({
          id: d.id,
          qty: d.jumlah || d.qty || 1,
          catatan: d.catatan,
          menu: d.menu ? {
            id: d.menu.id,
            nama: d.menu.nama_menu || d.menu.nama || 'Menu',
            nama_menu: d.menu.nama_menu || d.menu.nama || 'Menu'
          } : {
            nama: d.nama_menu || 'Menu',
            nama_menu: d.nama_menu || 'Menu'
          }
        }));
      }
      this.checkerPreviewBill = targetBill;
      this.showCheckerPreviewModal = true;
    },

    closeCheckerPreview() {
      this.showCheckerPreviewModal = false;
      this.checkerPreviewBill = null;
      this.showSuccessModal = false;
    },

    formatDateStr(dateStr, full = false) {
      if (!dateStr) return '-';
      try {
        const s = String(dateStr).replace(' ', 'T');
        const d = new Date(s);
        if (isNaN(d.getTime())) return String(dateStr);
        const day = d.getDate();
        const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const fullMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const month = full ? fullMonths[d.getMonth()] : shortMonths[d.getMonth()];
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const mins = String(d.getMinutes()).padStart(2, '0');
        return `${day} ${month} ${year}, ${hours}.${mins} WIB`;
      } catch (e) {
        return String(dateStr);
      }
    },

    printCheckerDirect() {
      if (this.checkerPreviewBill && this.checkerPreviewBill.id) {
        this.printReceiptPopup('/pos/dinein/pesanan/' + this.checkerPreviewBill.id + '/print-gabungan?auto_print=1', 'PrintChecker_' + this.checkerPreviewBill.id);
      } else {
        const printContent = document.getElementById('printableCheckerArea');
        if (!printContent) {
          window.print();
          return;
        }
        window.print();
      }
    },


    openVoidModal(trx) {
      this.trxToVoid = trx;
      this.alasanVoidInput = 'Salah Input Menu';
      this.catatanVoidInput = '';
      this.showVoidModal = true;
    },

    async submitVoidOrder() {
      if (!this.trxToVoid) return;
      this.isSubmitting = true;
      try {
        const res = await fetch(`/pos/dinein/pesanan/${this.trxToVoid.id}/void`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify({
            alasan_void: this.alasanVoidInput,
            catatan: this.catatanVoidInput
          })
        });
        const data = await res.json();
        if (res.ok && data.success) {
          this.showVoidModal = false;
          const found = this.riwayatTransaksi.find(t => t.id === this.trxToVoid.id);
          if (found) {
            found.status = 'void';
            if (found.pembayaran) found.pembayaran.status = 'void';
          }
          Swal.fire({ icon: 'success', title: 'Transaksi Dibatalkan (Void)!', text: data.message, confirmButtonColor: '#0D3024' });
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal void transaksi', confirmButtonColor: '#0D3024' });
        }
      } catch(e) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Terjadi kesalahan jaringan.', confirmButtonColor: '#0D3024' });
      } finally {
        this.isSubmitting = false;
      }
    },

    exportToCsv() {
      const rows = [
        ['Kode Pesanan', 'Pelanggan', 'Meja', 'Waktu', 'Kasir', 'Metode Bayar', 'Status', 'Total Tagihan (Rp)']
      ];
      this.filteredRiwayat.forEach(t => {
        const total = (t.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0);
        rows.push([
          t.id_pesanan || ('DIN-' + t.id),
          `"${t.nama_konsumen || ''}"`,
          t.meja ? t.meja.nomor_meja : '-',
          t.created_at || '',
          `"${t.kasir_name || 'Kasir'}"`,
          t.metode_bayar || 'LUNAS',
          t.status.toUpperCase(),
          total
        ]);
      });
      const csvContent = 'data:text/csv;charset=utf-8,' + rows.map(e => e.join(',')).join('\n');
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', `Riwayat_Transaksi_Kasir_${(new Date()).toISOString().slice(0,10)}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },

    // Table
    emptyTablesCount: {{ $mejas->where('status', 'kosong')->count() }},
    selectedTable: null,
    selectedTableLabel: '',

    // Customer info
    customerName: '',
    customerPhone: '',

    // Cart
    cart: [],
    isSubmitting: false,

    // Receipt & Right Panel State
    rightPanelMode: 'cart', // 'cart' or 'receipt'
    receiptTab: 'all', // 'all', 'dapur', 'meja'
    savedPesananId: null,
    savedPesananObject: null,
    activePrintEmbed: 'gabungan',

    // Pengaturan Biaya Layanan Flat Nominal per Transaksi/Struk
    pajakAktif: false,
    persentasePajak: 0,
    layananAktif: {{ ($pengaturanTransaksi->layanan_aktif ?? true) ? 'true' : 'false' }},
    nominalLayanan: {{ (float) ($pengaturanTransaksi->nominal_layanan ?? 1000) }},

    openBills: @json($openBills ?? []),

    // ── Computed ────────────────────────────────────
    get subTotal()   { return this.cart.reduce((t, i) => t + i.harga * i.qty, 0); },
    get totalServiceFee() { return (this.layananAktif && this.subTotal > 0) ? this.nominalLayanan : 0; },
    get totalPajak() { return 0; },
    get totalPrice() { return this.subTotal + this.totalServiceFee; },
    get totalQty()   { return this.cart.reduce((t, i) => t + i.qty, 0); },

    formatPrice(n) {
      if (!n) return '0';
      return Number(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    printSilentIframe(url) {
      if (!url) return;
      // Add cache buster
      const separator = url.includes('?') ? '&' : '?';
      const noCacheUrl = url + separator + '_t=' + new Date().getTime();

      let iframe = document.getElementById('posPrintIframe');
      if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'posPrintIframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '270px'; // Set width to typical receipt width just in case
        iframe.style.height = '100px';
        iframe.style.border = '0';
        iframe.style.visibility = 'hidden';
        iframe.style.zIndex = '-9999';
        document.body.appendChild(iframe);
      }
      iframe.src = noCacheUrl;
      iframe.onload = function() {
        setTimeout(() => {
          try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
          } catch(e) { console.error(e); }
        }, 300);
      };
    },

    resetCartPanel() {
      this.cart = [];
      this.customerName = '';
      this.customerPhone = '';
      this.selectedTable = null;
      this.selectedTableLabel = '';
      this.savedPesananId = null;
      this.savedPesananObject = null;
      this.rightPanelMode = 'cart';
      this.receiptTab = 'all';
    },

    printReceiptPopup(url, windowName = null) {
      if (!url) return;
      const targetName = windowName || ('PrintThermalPopup_' + Date.now());
      const features = 'popup=yes,width=400,height=620,top=80,left=80,toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes';
      const win = window.open(url, targetName, features);
      if (win) {
        win.focus();
      }
    },

    printCombinedReceipt(pesananId) {
      if (!pesananId) return;
      this.printReceiptPopup('/pos/dinein/pesanan/' + pesananId + '/print-gabungan', 'PrintCombined_' + pesananId);
    },

    printBothReceipts(pesananId) {
      if (!pesananId) return;
      this.printReceiptPopup('/pos/dinein/pesanan/' + pesananId + '/print-dapur', 'PrintDapur_' + pesananId);
      setTimeout(() => {
        this.printReceiptPopup('/pos/dinein/pesanan/' + pesananId + '/print-meja', 'PrintMeja_' + pesananId);
      }, 350);
    },

    getTimeAgo(dateStr) {
      if (!dateStr) return '';
      const past = new Date(dateStr);
      if (isNaN(past.getTime())) return '';
      const now = new Date();
      const diffMins = Math.max(0, Math.floor((now - past) / 60000));
      if (diffMins < 1) return 'Baru saja';
      if (diffMins < 60) return diffMins + 'm lalu';
      const diffHours = Math.floor(diffMins / 60);
      return diffHours + 'j ' + (diffMins % 60) + 'm';
    },

    // ── Cart ────────────────────────────────────────
    addToCart(menuId, nama, harga) {
      const existing = this.cart.find(i => i.menu_id === menuId);
      if (existing) { existing.qty++; }
      else { this.cart.unshift({ menu_id: menuId, nama, harga, qty: 1, catatan: '' }); }
    },
    updateQty(index, change) {
      const qty = this.cart[index].qty + change;
      if (qty > 0) this.cart[index].qty = qty;
      else this.removeFromCart(index);
    },
    removeFromCart(index) { this.cart.splice(index, 1); },

    // ── Table ───────────────────────────────────────
    selectTable(id, label) {
      this.selectedTable = id;
      this.selectedTableLabel = label;
      this.leftView = 'menu';
    },

    confirmClearTable(mejaId, nomorMeja) {
      if (this.openBills.some(b => b.meja_id == mejaId)) {
        Swal.fire({
          title: 'Meja Memiliki Tagihan Aktif',
          text: nomorMeja + ' masih memiliki tagihan aktif yang belum dibayar. Lanjutkan ke pembayaran?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#0D3024',
          confirmButtonText: 'Bayar Sekarang',
          cancelButtonText: 'Batal'
        }).then(res => {
          if (res.isConfirmed) {
            window.location.href = '/pos/dinein/meja/' + mejaId + '/checkout';
          }
        });
      } else {
        const form = document.getElementById('form-clear-' + mejaId);
        if (form) form.submit();
      }
    },

    changeSubStatus(pesananId, subStatus) {
      const bill = this.openBills.find(b => b.id == pesananId);
      if (bill) {
        bill.status_pesanan_id = subStatus;
      }
      fetch('/pos/dinein/pesanan/' + pesananId + '/sub-status', {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status_pesanan_id: subStatus })
      }).catch(e => console.error(e));
    },

    async toggleMenuStatus(menuId) {
      try {
        const res = await fetch('/pos/menu/' + menuId + '/toggle-status', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        });
        const data = await res.json();
        if (res.ok && data.success) {
          window.location.reload();
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal mengubah status ketersediaan menu', confirmButtonColor: '#0D3024' });
        }
      } catch(e) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Terjadi kesalahan koneksi internet / server.', confirmButtonColor: '#0D3024' });
      }
    },

    // ── Submit Order ────────────────────────────────
    async submitOrder(action) {
      if (!this.selectedTable) return Swal.fire({ icon: 'warning', title: 'Pilih Meja', text: 'Mohon pilih nomor meja terlebih dahulu!', confirmButtonColor: '#0D3024' });
      if (!this.customerName.trim()) return Swal.fire({ icon: 'warning', title: 'Nama Konsumen Kosong', text: 'Mohon isi nama konsumen terlebih dahulu!', confirmButtonColor: '#0D3024' });
      if (!this.cart.length) return Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Keranjang belanjaan masih kosong!', confirmButtonColor: '#0D3024' });

      this.isSubmitting = true;
      try {
        const res = await fetch('{{ route('pos.dinein.store-pos') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify({
            meja_id: this.selectedTable,
            nama_konsumen: this.customerName.trim(),
            no_telepon: this.customerPhone ? this.customerPhone.trim() : '',
            items: this.cart
          })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            // Dynamic Alpine state update without page reload
            let pesananObj = data.pesanan;
            if (pesananObj) {
              pesananObj.nama_konsumen = pesananObj.nama_konsumen || this.customerName.trim();
              pesananObj.no_telepon = pesananObj.no_telepon || (this.customerPhone ? this.customerPhone.trim() : '-');
              pesananObj.sumber_pesanan = pesananObj.sumber_pesanan || 'pos';
              pesananObj.metode_pemesanan = pesananObj.metode_pemesanan || 'Pemesanan via Kasir';

              if (!pesananObj.items && pesananObj.detail_pesanan) {
                pesananObj.items = pesananObj.detail_pesanan.map(d => ({
                  id: d.id,
                  qty: d.jumlah || d.qty || 1,
                  catatan: d.catatan,
                  harga_satuan: d.harga_satuan,
                  subtotal: d.subtotal || ((d.harga_satuan || (d.menu ? (d.menu.harga_jual || d.menu.harga || 0) : 0)) * (d.jumlah || d.qty || 1)),
                  menu: d.menu ? {
                    id: d.menu.id,
                    nama: d.menu.nama_menu || d.menu.nama || 'Menu',
                    nama_menu: d.menu.nama_menu || d.menu.nama || 'Menu',
                    harga: d.menu.harga_jual || d.menu.harga || 0
                  } : {
                    nama: d.nama_menu || 'Menu',
                    nama_menu: d.nama_menu || 'Menu',
                    harga: d.harga_satuan || 0
                  }
                }));
              }
              if (Array.isArray(this.openBills)) {
                const existingIdx = this.openBills.findIndex(b => b.id === pesananObj.id);
                if (existingIdx !== -1) {
                  this.openBills[existingIdx] = pesananObj;
                } else {
                  this.openBills.unshift(pesananObj);
                }
              } else {
                this.openBills = Object.values(this.openBills || {});
                this.openBills.unshift(pesananObj);
              }
            }

            if (this.emptyTablesCount > 0) {
              this.emptyTablesCount = Math.max(0, this.emptyTablesCount - 1);
            }

            this.savedPesananId = data.pesanan_id;
            this.savedPesananObject = pesananObj || data.pesanan;

            // Toast Success Message
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
            Toast.fire({
                icon: 'success',
                title: data.message || 'Pesanan berhasil disimpan'
            });

            // Print Struk untuk aksi Simpan
            if (action === 'simpan') {
              this.showSuccessModal = true;
            } else if (action === 'bayar') {
              window.location.href = `/pos/dinein/meja/${this.selectedTable}/checkout`;
            }

            // Reset cart & form inputs smoothly
            this.cart = [];
            this.customerName = '';
            this.customerPhone = '';
            this.selectedTable = null;
            this.selectedTableLabel = '';

        } else { Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: data.message || 'Gagal menyimpan pesanan', confirmButtonColor: '#0D3024' }); }
      } catch(e) { Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Terjadi kesalahan jaringan.', confirmButtonColor: '#0D3024' }); }
      finally { this.isSubmitting = false; }
    },

    async toggleStatusSajian(billId, itemId) {
        try {
            const res = await fetch('/pos/dinein/item/' + itemId + '/toggle-sajian', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await res.json();
            if (res.ok && data.success) {
                // Update local state (openBills + checker modal + mejaStatusMap)
                const bill = this.openBills.find(b => b.id === billId);
                if (bill && bill.items) {
                    const item = bill.items.find(i => i.id === itemId);
                    if (item) {
                        item.status_item = data.status_item;
                    }
                }
                if (this.checkerBill && this.checkerBill.items) {
                    const cItem = this.checkerBill.items.find(i => i.id === itemId);
                    if (cItem) {
                        cItem.status_item = data.status_item;
                    }
                }
                Object.values(this.mejaStatusMap).forEach(m => {
                    if (m.active_order && m.active_order.items) {
                        const item = m.active_order.items.find(i => i.id === itemId);
                        if (item) item.status_item = data.status_item;
                    }
                });
            }
        } catch(e) {
            console.error('Toggle sajian error:', e);
        }
    },

    async updateOrderStatus(billId, newStatusId) {
        try {
            const res = await fetch('/admin/pesanan/dinein/' + billId + '/update-status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status_pesanan_id: newStatusId })
            });
            if (res.ok) {
                window.location.reload();
            }
        } catch(e) {
            console.error('Update status error:', e);
        }
    },

    openChecker(bill) {
        this.checkerBill = bill;
        this.showCheckerModal = true;
    },

    proceedToCheckout(bill) {
        if (!bill) return;
        const mejaId = bill.meja_id || (bill.meja ? (typeof bill.meja === 'object' ? bill.meja.id : bill.meja) : null);
        if (mejaId) {
            window.location.href = '/pos/dinein/meja/' + mejaId + '/checkout';
        } else if (bill.id) {
            window.location.href = '/pos/dinein/meja/' + bill.id + '/checkout';
        }
    },

    // ── Real-time Table Status Polling ─────────────────
    startTableStatusPolling() {
        if (this.tableStatusPolling) clearInterval(this.tableStatusPolling);
        this.fetchTableStatus();
        this.tableStatusPolling = setInterval(() => this.fetchTableStatus(), 5000);
    },

    async fetchTableStatus() {
        try {
            const res = await fetch('{{ route("pos.dinein.table-status") }}');
            const data = await res.json();
            if (data.success && data.mejas) {
                this.mejaStatusMap = {};
                data.mejas.forEach(m => {
                    this.mejaStatusMap[m.id] = m;
                });

                if (data.open_bills) {
                    if (this.openBills.length > 0 && data.open_bills.length > 0) {
                        const currentLatest = this.openBills[0].id;
                        const newLatest = data.open_bills[0].id;
                        if (newLatest > currentLatest) {
                            data.open_bills.forEach(bill => {
                                if (bill.id > currentLatest) {
                                    bill.is_new = true;
                                    setTimeout(() => {
                                        const b = this.openBills.find(x => x.id === bill.id);
                                        if (b) b.is_new = false;
                                    }, 15000);
                                }
                            });
                            try {
                                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                const osc = ctx.createOscillator();
                                const gainNode = ctx.createGain();
                                osc.type = 'sine';
                                osc.frequency.setValueAtTime(880, ctx.currentTime);
                                gainNode.gain.setValueAtTime(0.1, ctx.currentTime);
                                osc.connect(gainNode);
                                gainNode.connect(ctx.destination);
                                osc.start();
                                osc.stop(ctx.currentTime + 0.2);
                            } catch(e) {}
                            
                            Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                            }).fire({
                                icon: 'info',
                                title: 'Ada Pesanan Baru!',
                                text: 'Pesanan #' + (data.open_bills[0].id_pesanan || data.open_bills[0].id) + ' telah masuk.'
                            });
                        }
                    }
                    this.openBills = data.open_bills;
                }
                // Update mejasList status
                this.mejasList = this.mejasList.map(m => {
                    const status = this.mejaStatusMap[m.id];
                    if (status) {
                        return { ...m, status: status.has_active_order ? 'terisi' : 'kosong' };
                    }
                    return m;
                });
            }
        } catch (e) {
            console.error('Failed to fetch table status:', e);
        }
    },

    stopTableStatusPolling() {
        if (this.tableStatusPolling) clearInterval(this.tableStatusPolling);
    },

    getKotStatusLabel(kotStatusId) {
        const labels = { 1: 'Menunggu', 2: 'Dimasak', 3: 'Selesai' };
        return labels[kotStatusId] || 'Unknown';
    },

    getKotStatusClass(kotStatusId) {
        const classes = { 1: 'bg-neutral-100 text-neutral-700 border-neutral-300', 2: 'bg-amber-100 text-amber-800 border-amber-300', 3: 'bg-emerald-100 text-emerald-800 border-emerald-300' };
        return classes[kotStatusId] || 'bg-neutral-100 text-neutral-700 border-neutral-300';
    },

    getStatusId(b) {
        if (!b) return 1;
        return Number(b.status_pesanan_id) || (b.status_raw === 'selesai' ? 5 : (b.status_raw === 'dibatalkan' ? 6 : 2));
    },

    getStatusLabel(id) {
        const labels = {
            1: 'Menunggu Konfirmasi',
            2: 'Dikonfirmasi',
            3: 'Sedang Diproses',
            4: 'Pesanan Siap',
            8: 'Pesanan Telah Dihidangkan',
            5: 'Selesai',
            6: 'Dibatalkan',
            7: 'Terjadwal'
        };
        return labels[id] || 'Dikonfirmasi';
    },

    getStatusClass(id) {
        const map = {
            1: 'text-amber-800 bg-amber-50 border-amber-200/90',
            2: 'text-blue-800 bg-blue-50 border-blue-200/90',
            3: 'text-indigo-800 bg-indigo-50 border-indigo-200/90',
            4: 'text-purple-800 bg-purple-50 border-purple-200/90',
            8: 'text-teal-800 bg-teal-50 border-teal-200/90',
            5: 'text-emerald-800 bg-emerald-50 border-emerald-200/90',
            6: 'text-rose-800 bg-rose-50 border-rose-200/90',
            7: 'text-sky-800 bg-sky-50 border-sky-200/90'
        };
        return map[id] || 'text-gray-700 bg-gray-50 border-gray-200';
    },

    getStatusDot(id) {
        const map = {
            1: 'bg-amber-500',
            2: 'bg-blue-500',
            3: 'bg-indigo-500 animate-pulse',
            4: 'bg-purple-500',
            8: 'bg-teal-500',
            5: 'bg-emerald-500',
            6: 'bg-rose-500',
            7: 'bg-sky-500'
        };
        return map[id] || 'bg-gray-400';
    },

    async konfirmasiPesanan(bill) {
        try {
            const res = await fetch(`/pos/dinein/pesanan/${bill.id}/konfirmasi`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                // Segarkan data tabel dan status
                this.fetchTableStatus();
                
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).fire({
                    icon: 'success',
                    title: 'Pesanan Dikonfirmasi'
                });

                // Tampilkan Pratinjau Cetak Checker di dalam modal
                this.openCheckerPreview(bill);
            } else {
                Swal.fire('Gagal', data.message || 'Gagal konfirmasi pesanan.', 'error');
            }
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        }
    },

    async hidangkanPesanan(bill) {
        const tableName = bill.meja ? (bill.meja.nomor_meja?.startsWith('Meja') ? bill.meja.nomor_meja : 'Meja ' + bill.meja.nomor_meja) : (bill.id_pesanan || 'pesanan ini');
        const result = await Swal.fire({
            title: 'Hidangkan Pesanan?',
            text: `Tandai seluruh hidangan ${tableName} telah selesai disajikan ke meja tamu?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Telah Dihidangkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch(`/pos/dinein/pesanan/${bill.id}/hidangkan`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                this.fetchTableStatus();
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }).fire({
                    icon: 'success',
                    title: 'Pesanan Telah Dihidangkan'
                });
            } else {
                Swal.fire('Gagal', data.message || 'Gagal mengubah status pesanan.', 'error');
            }
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        }
    }
  };
}

window.posSystem = posSystemData;
if (typeof Alpine !== 'undefined') {
  Alpine.data('posSystem', posSystemData);
}
document.addEventListener('alpine:init', () => {
  if (typeof Alpine !== 'undefined') {
    Alpine.data('posSystem', posSystemData);
  }
});
</script>

<div x-data="posSystemData()" x-init="startTableStatusPolling()" class="pos-root min-h-[calc(100vh-4rem)] lg:h-[calc(100vh-4rem)] w-full flex flex-col lg:flex-row lg:overflow-hidden bg-secondary-soft text-body">

  {{-- ─────────────────────────────── LEFT PANEL ────────────────────────────── --}}
  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">

    {{-- ── TOPBAR ──────────────────────────────────────────────────── --}}
    <header class="bg-white px-6 py-3.5 shrink-0 z-30 space-y-3">

      {{-- BARIS 1: Header Top Bar (Judul & Tabs) --}}
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Brand Mark --}}
        <div class="shrink-0">
          <x-ui.page-header title="Point of Sale" subtitle="Katalog menu, pesanan dine in, riwayat transaksi, dan manajemen meja." :breadcrumbs="['Penjualan', 'Dine In']" />
        </div>

        {{-- TAB UTAMA (Katalog Menu, List Pesanan, Riwayat, Meja) --}}
        <x-ui.tab-list align="right">
          {{-- Tab 1: Katalog Menu --}}
          @if(!$isPelayan)
          <x-ui.tab @click="leftView = 'menu'"
                    x-bind:class="leftView === 'menu' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-primary hover:border-primary/40'">
            Katalog Menu
          </x-ui.tab>
          @endif

          {{-- Tab 2: Daftar Pesanan Dine-in Aktif --}}
          <x-ui.tab @click="leftView = 'open_bills'"
                    x-bind:class="leftView === 'open_bills' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-primary hover:border-primary/40'">
            <span class="inline-flex items-center gap-2">
              <span>Daftar Pesanan Dine-in Aktif</span>
              {{-- Badge Angka (Pill) --}}
              <span class="px-1.5 py-0.5 rounded-full text-xs font-semibold transition-colors"
                    x-bind:class="leftView === 'open_bills' ? 'bg-neutral-900 text-white' : 'bg-neutral-100 text-neutral-500'"
                    x-text="openBills.length"></span>
            </span>
          </x-ui.tab>

          {{-- Tab 3: Meja --}}
          <x-ui.tab @click="leftView = 'meja'"
                    x-bind:class="leftView === 'meja' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-primary hover:border-primary/40'">
            Meja
          </x-ui.tab>

        </x-ui.tab-list>
      </div>

      {{-- BARIS 2: Search & Filters --}}
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-1 pb-0.5">
        
        {{-- Area Kiri: Search Input --}}
        <div class="relative w-full md:w-64 shrink-0">
          <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-gray-400 pointer-events-none" style="width: 16px; height: 16px;" />
          <input x-show="leftView === 'menu'" x-cloak x-model="searchQuery" type="text" placeholder="Cari menu…"
                 class="w-full h-9 pl-9 pr-7 text-sm font-medium rounded-xl border border-neutral-200 bg-white focus:border-neutral-900 transition-all outline-none">
          
          <input x-show="leftView === 'meja'" x-cloak x-model="tableSearch" type="text" placeholder="Cari meja…"
                 class="w-full h-9 pl-9 pr-7 text-sm font-medium rounded-xl border border-neutral-200 bg-white focus:border-neutral-900 transition-all outline-none">
          
          <input x-show="leftView === 'open_bills'" x-cloak x-model="openBillSearch" type="text" placeholder="Cari pesanan…"
                 class="w-full h-9 pl-9 pr-7 text-sm font-medium rounded-xl border border-neutral-200 bg-white focus:border-neutral-900 transition-all outline-none">
          <button x-show="(leftView === 'menu' && searchQuery) || (leftView === 'meja' && tableSearch) || (leftView === 'open_bills' && openBillSearch)" x-cloak
                  @click="searchQuery = ''; tableSearch = ''; openBillSearch = ''"
                  class="absolute right-2.5 top-1.5 text-gray-400 hover:text-gray-600 text-sm leading-none">&times;</button>
        </div>

        {{-- Area Kanan: Filters --}}
        <div class="flex-1 min-w-0">
          <div x-show="leftView === 'menu'" x-cloak class="flex overflow-x-auto no-scrollbar gap-2">
            <button @click="activeCategory = 'semua'"
                    :class="activeCategory === 'semua' ? 'chip-active shadow-xs' : 'chip-default'"
                    class="shrink-0 inline-flex items-center px-4 h-9 rounded-xl text-xs font-extrabold transition-all hover:scale-[1.02]">
              <span>Semua Menu</span>
            </button>
            @foreach($kategoris as $kategori)
            <button @click="activeCategory = '{{ $kategori->id }}'"
                    :class="activeCategory === '{{ $kategori->id }}' ? 'chip-active shadow-xs' : 'chip-default'"
                    class="shrink-0 inline-flex items-center px-4 h-9 rounded-xl text-xs font-extrabold whitespace-nowrap transition-all hover:scale-[1.02]">
              {{ $kategori->nama_kategori }}
            </button>
            @endforeach
          </div>

          <div x-show="leftView === 'meja'" x-cloak class="flex overflow-x-auto no-scrollbar gap-2">
            <button @click="tableFilter = 'semua'"
                    :class="tableFilter === 'semua' ? 'chip-active shadow-xs' : 'chip-default'"
                    class="shrink-0 inline-flex items-center px-4 h-9 rounded-xl text-xs font-extrabold transition-all hover:scale-[1.02]">
              <span>Semua</span>
            </button>
            <button @click="tableFilter = 'kosong'"
                    :class="tableFilter === 'kosong' ? 'chip-active shadow-xs' : 'chip-default'"
                    class="shrink-0 inline-flex items-center gap-1.5 px-4 h-9 rounded-xl text-xs font-extrabold transition-all hover:scale-[1.02]">
              <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
              <span>Kosong</span>
            </button>
            <button @click="tableFilter = 'terisi'"
                    :class="tableFilter === 'terisi' ? 'chip-active shadow-xs' : 'chip-default'"
                    class="shrink-0 inline-flex items-center gap-1.5 px-4 h-9 rounded-xl text-xs font-extrabold transition-all hover:scale-[1.02]">
              <span class="w-2 h-2 rounded-full bg-amber-600 shrink-0"></span>
              <span>Terisi</span>
            </button>
          </div>

          <div x-show="leftView === 'open_bills'" class="flex items-center gap-2">
            
            {{-- Dropdown Filter: Metode Pemesanan --}}
            <div x-data="{ open: false }" class="relative shrink-0" @click.outside="open = false">
              <button type="button" @click="open = !open" 
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-900/20 transition-all cursor-pointer">
                <span>Metode Pemesanan</span>
                <span x-show="openBillFilter !== 'semua'" x-cloak
                      class="flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary rounded-full shadow-sm">1</span>
                <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 transition-transform duration-200" x-bind:class="{'rotate-180': open}" />
              </button>
              
              <div x-show="open" 
                   x-transition:enter="transition ease-out duration-100"
                   x-transition:enter-start="transform opacity-0 scale-95"
                   x-transition:enter-end="transform opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-75"
                   x-transition:leave-start="transform opacity-100 scale-100"
                   x-transition:leave-end="transform opacity-0 scale-95"
                   class="absolute z-[100] w-56 py-2 mt-2 bg-white border border-gray-100 shadow-xl rounded-xl left-0"
                   style="display: none;">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilih metode pemesanan</div>
                <div class="flex flex-col gap-1 px-2 max-h-64 overflow-y-auto">
                  <template x-for="opt in [
                    {v: 'semua', l: 'Semua Status'},
                    {v: 'pos', l: 'Pemesanan via Kasir'},
                    {v: 'qr', l: 'Self-Order QR'}
                  ]" :key="opt.v">
                    <label class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors group">
                      <div class="relative flex items-center justify-center w-5 h-5">
                        <input type="radio" name="pos_open_bill_filter" :value="opt.v" x-model="openBillFilter" @change="open = false"
                               class="peer absolute w-5 h-5 opacity-0 cursor-pointer">
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full bg-white peer-checked:bg-emerald-500 peer-checked:border-emerald-500 flex items-center justify-center transition-colors group-hover:border-emerald-400">
                          <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        </div>
                      </div>
                      <span class="font-medium group-hover:text-emerald-700 transition-colors" x-text="opt.l"></span>
                    </label>
                  </template>
                </div>
              </div>
            </div>

            {{-- Dropdown Filter: Status Pesanan --}}
            <div x-data="{ open: false }" class="relative shrink-0" @click.outside="open = false">
              <button type="button" @click="open = !open" 
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-900/20 transition-all cursor-pointer">
                <span>Status Pesanan</span>
                <span x-show="openBillStatusFilter !== 'semua'" x-cloak
                      class="flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary rounded-full shadow-sm">1</span>
                <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 transition-transform duration-200" x-bind:class="{'rotate-180': open}" />
              </button>
              
              <div x-show="open" 
                   x-transition:enter="transition ease-out duration-100"
                   x-transition:enter-start="transform opacity-0 scale-95"
                   x-transition:enter-end="transform opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-75"
                   x-transition:leave-start="transform opacity-100 scale-100"
                   x-transition:leave-end="transform opacity-0 scale-95"
                   class="absolute z-[100] w-56 py-2 mt-2 bg-white border border-gray-100 shadow-xl rounded-xl left-0"
                   style="display: none;">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilih status pesanan</div>
                <div class="flex flex-col gap-1 px-2 max-h-64 overflow-y-auto">
                  <template x-for="opt in [
                    {v: 'semua', l: 'Semua Status'},
                    {v: 'Menunggu Konfirmasi', l: 'Menunggu Konfirmasi'},
                    {v: 'Dikonfirmasi', l: 'Dikonfirmasi'},
                    {v: 'Sedang Diproses', l: 'Sedang Diproses'},
                    {v: 'Pesanan Siap', l: 'Pesanan Siap'},
                    {v: 'Pesanan Telah Dihidangkan', l: 'Pesanan Telah Dihidangkan'},
                    {v: 'Selesai', l: 'Selesai'},
                    {v: 'Dibatalkan', l: 'Dibatalkan'}
                  ]" :key="opt.v">
                    <label class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors group">
                      <div class="relative flex items-center justify-center w-5 h-5">
                        <input type="radio" name="pos_open_bill_status_filter" :value="opt.v" x-model="openBillStatusFilter" @change="open = false"
                               class="peer absolute w-5 h-5 opacity-0 cursor-pointer">
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full bg-white peer-checked:bg-emerald-500 peer-checked:border-emerald-500 flex items-center justify-center transition-colors group-hover:border-emerald-400">
                          <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        </div>
                      </div>
                      <span class="font-medium group-hover:text-emerald-700 transition-colors" x-text="opt.l"></span>
                    </label>
                  </template>
                </div>
              </div>
            </div>

          </div>

          <div x-show="leftView === 'riwayat'" class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            <button type="button" @click="riwayatDateQuick = 'today'"
                    :class="riwayatDateQuick === 'today' ? 'chip-active shadow-2xs' : 'chip-default'"
                    class="px-3.5 h-9 rounded-xl text-xs font-extrabold transition-all shrink-0">Hari Ini</button>
            <button type="button" @click="riwayatDateQuick = 'yesterday'"
                    :class="riwayatDateQuick === 'yesterday' ? 'chip-active shadow-2xs' : 'chip-default'"
                    class="px-3.5 h-9 rounded-xl text-xs font-extrabold transition-all shrink-0">Kemarin</button>
            <button type="button" @click="riwayatDateQuick = 'last7'"
                    :class="riwayatDateQuick === 'last7' ? 'chip-active shadow-2xs' : 'chip-default'"
                    class="px-3.5 h-9 rounded-xl text-xs font-extrabold transition-all shrink-0">7 Hari Terakhir</button>
            
            <div class="h-5 w-px bg-gray-300 mx-1 shrink-0"></div>

            <div class="relative">
              <select x-model="riwayatStatusFilter" class="appearance-none h-9 pl-3.5 pr-8 text-xs font-bold rounded-xl border border-gray-200 bg-white text-gray-700 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 shadow-xs cursor-pointer">
                <option value="semua">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="menunggu_pembayaran">Pending</option>
                <option value="void">Void / Batal</option>
              </select>
              <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                <x-heroicon-o-chevron-down class="w-3.5 h-3.5" />
              </span>
            </div>

            <div class="relative">
              <select x-model="riwayatPayFilter" class="appearance-none h-9 pl-3.5 pr-8 text-xs font-bold rounded-xl border border-gray-200 bg-white text-gray-700 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 shadow-xs cursor-pointer">
                <option value="semua">Semua Bayar</option>
                <option value="cash">Tunai (Cash)</option>
                <option value="qris">Nontunai (QRIS)</option>
              </select>
              <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                <x-heroicon-o-chevron-down class="w-3.5 h-3.5" />
              </span>
            </div>

            <button type="button" @click="exportToCsv()"
                    title="Export CSV"
                    class="h-9 px-3.5 rounded-xl bg-primary hover:bg-primary-container text-white font-extrabold text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
              <x-heroicon-o-arrow-down-tray class="w-3 h-3" />
              <span>Export CSV</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    {{-- ══════════════════════  VIEW 1 · MENU CATALOG  ══════════════════════ --}}
    <div x-show="leftView === 'menu'" x-cloak class="flex-1 overflow-y-auto p-4 bg-white">
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
        @foreach($menus as $menu)
        @php 
            $isHabis = $menu->status_aktif == false || in_array($menu->id, $menuHabisIds ?? []);
            $isBahanHabis = in_array($menu->id, $menuHabisIds ?? []) && $menu->status_aktif == true;
        @endphp
        <div x-show="(activeCategory === 'semua' || activeCategory == '{{ $menu->kategori_menu_id }}') && ('{{ strtolower(addslashes($menu->nama_menu)) }}'.includes(searchQuery.toLowerCase()))"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @if(!$isHabis) @click="addToCart({{ $menu->id }}, '{{ addslashes($menu->nama_menu) }}', {{ $menu->harga_jual }})" @endif
             class="group cursor-pointer flex flex-col bg-white border border-gray-100 shadow-2xs hover:shadow-md hover:-translate-y-0.5 hover:border-gray-300 transition-all duration-300 rounded-xl overflow-hidden {{ $isHabis ? 'opacity-50 grayscale pointer-events-none select-none' : '' }}">

          {{-- Thumbnail --}}
          <div class="relative w-full aspect-[4/3] bg-gray-50 border-b border-gray-100 overflow-hidden">
            @if($menu->foto)
              <img src="{{ Storage::url($menu->foto) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 {{ $isHabis ? 'grayscale opacity-60' : '' }}" alt="{{ $menu->nama_menu }}">
            @else
              @php
                  $words = explode(' ', $menu->nama_menu);
                  $initials = '';
                  foreach (array_slice($words, 0, 3) as $w) {
                      $initials .= strtoupper(substr($w, 0, 1));
                  }
              @endphp
              <div class="w-full h-full flex items-center justify-center bg-gray-50 {{ $isHabis ? 'grayscale opacity-60' : '' }}">
                  <span class="text-2xl font-black text-gray-300 tracking-widest">{{ $initials }}</span>
              </div>
            @endif

            {{-- Category Label --}}
            <span class="absolute top-2 left-2 bg-white/90 backdrop-blur-xs text-neutral-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-gray-100 max-w-[85%] truncate" title="{{ $menu->kategori_menu->nama_kategori ?? 'Menu' }}">
              {{ $menu->kategori_menu->nama_kategori ?? 'Menu' }}
            </span>

            {{-- Overlay Habis Badge --}}
            @if($isHabis)
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-[1px] flex items-center justify-center">
              <span class="bg-red-600 text-white text-[10px] font-black px-2.5 py-1 rounded-lg shadow-md tracking-wider uppercase">
                {{ $isBahanHabis ? 'BAHAN HABIS' : 'HABIS' }}
              </span>
            </div>
            @endif
          </div>

          {{-- Info --}}
          <div class="p-3 flex-1 flex flex-col justify-between gap-1.5">
            <div>
              <p class="text-xs font-bold text-body leading-snug line-clamp-2" title="{{ $menu->nama_menu }}">{{ $menu->nama_menu }}</p>
            </div>
            <div class="flex items-center justify-between mt-auto pt-1">
              <div>
                <span class="text-xs font-extrabold text-primary block">Rp {{ number_format($menu->harga_jual, 0, ',', '.') }}</span>
              </div>
              @if(!$isHabis)
              <button type="button" class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary-container hover:text-white transition-colors shrink-0">
                <x-heroicon-o-plus class="w-3 h-3" />
              </button>
              @endif
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- ══════════════════════  VIEW 2 · MANAJEMEN MEJA  ══════════════════════ --}}
    <div x-show="leftView === 'meja'" x-cloak class="flex-1 overflow-y-auto p-4 md:p-6 pb-12 bg-secondary-soft space-y-5">

      {{-- TOP STAT CARDS SUMMARY (MINIMALIST) --}}
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        {{-- Card 1: Total Meja --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-2xs transition-all hover:border-gray-300">
          <span class="text-[11px] font-bold text-gray-400 block uppercase tracking-wider truncate">Total Meja</span>
          <div class="flex items-baseline gap-1 mt-1">
            <span class="text-xl font-extrabold text-gray-900 leading-none">{{ $mejas->count() }}</span>
            <span class="text-xs font-semibold text-gray-500">Meja</span>
          </div>
        </div>

        {{-- Card 2: Meja Tersedia (Kosong) --}}
        <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-2xs transition-all hover:border-emerald-200">
          <span class="text-[11px] font-bold text-emerald-700 block uppercase tracking-wider truncate">Tersedia</span>
          <div class="flex items-baseline gap-1 mt-1">
            <span class="text-xl font-extrabold text-emerald-700 leading-none" x-text="mejasList.filter(m => m.status === 'kosong').length"></span>
            <span class="text-xs font-semibold text-emerald-600">Meja</span>
          </div>
        </div>

        {{-- Card 3: Meja Terisi --}}
        <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-2xs transition-all hover:border-amber-200">
          <span class="text-[11px] font-bold text-amber-700 block uppercase tracking-wider truncate">Sedang Terisi</span>
          <div class="flex items-baseline gap-1 mt-1">
            <span class="text-xl font-extrabold text-amber-800 leading-none" x-text="mejasList.filter(m => m.status !== 'kosong').length"></span>
            <span class="text-xs font-semibold text-amber-600">Meja</span>
          </div>
        </div>

        {{-- Card 4: KOT Dapur --}}
        <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-2xs transition-all hover:border-blue-200">
          <span class="text-[11px] font-bold text-blue-700 block uppercase tracking-wider truncate">KOT Dapur Aktif</span>
          <div class="flex items-baseline gap-1 mt-1">
            <span class="text-xl font-extrabold text-blue-800 leading-none" x-text="Object.values(mejaStatusMap).filter(m => m.kot).length"></span>
            <span class="text-xs font-semibold text-blue-600">Tiket</span>
          </div>
        </div>
      </div>

      {{-- TOOLBAR: Section Header & Layout Switcher --}}
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div class="flex items-center gap-2">
          <h2 class="text-sm font-bold text-gray-900 tracking-wide">Daftar & Status Meja</h2>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white text-gray-600 border border-gray-200/80 shadow-2xs"
                x-text="(tableFilter === 'semua' ? 'Semua (' + mejasList.length + ')' : (tableFilter === 'kosong' ? 'Kosong (' + mejasList.filter(m => m.status === 'kosong').length + ')' : 'Terisi (' + mejasList.filter(m => m.status !== 'kosong').length + ')'))"></span>
        </div>

        {{-- Toggle Layout Switcher --}}
        <div class="flex items-center self-end sm:self-auto bg-white p-1 rounded-xl border border-gray-200/80 shadow-2xs">
          <button type="button" @click="mejaLayoutMode = 'grid'"
                  :class="mejaLayoutMode === 'grid' ? 'bg-primary text-white shadow-xs font-bold' : 'text-gray-500 hover:text-gray-900 font-semibold'"
                  class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
            Grid Kartu
          </button>
          <button type="button" @click="mejaLayoutMode = 'table'"
                  :class="mejaLayoutMode === 'table' ? 'bg-primary text-white shadow-xs font-bold' : 'text-gray-500 hover:text-gray-900 font-semibold'"
                  class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
            Tabel
          </button>
        </div>
      </div>

      {{-- ───────────────── MODE 1: GRID CARD VIEW (DENAH KARTU MEJA MINIMALIS) ───────────────── --}}
      <div x-show="mejaLayoutMode === 'grid'" x-transition class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($mejas as $meja)
        @php
          $openBillMeja = $openBills->firstWhere('meja_id', $meja->id);
          $subStatusMeja = $openBillMeja->status_pesanan_id ?? 'kosong';
        @endphp
        <div x-show="(tableFilter === 'semua' || (tableFilter === 'kosong' && '{{ $meja->status }}' === 'kosong') || (tableFilter === 'terisi' && '{{ $meja->status }}' !== 'kosong') || (tableFilter === '{{ $subStatusMeja }}')) && (!tableSearch || '{{ strtolower(addslashes($meja->nomor_meja)) }}'.includes(tableSearch.toLowerCase()))"
             class="group relative bg-white rounded-2xl border transition-all duration-200 flex flex-col justify-between overflow-hidden shadow-2xs hover:shadow-sm {{ $meja->status !== 'kosong' ? 'border-amber-200/90 bg-amber-50/15' : 'border-gray-200/80 hover:border-emerald-300' }}">
          
          {{-- Card Header --}}
          <div class="p-4 pb-3">
            <div class="flex items-start justify-between gap-2 mb-3">
              <div>
                <h3 class="text-base font-bold text-gray-900 leading-snug">{{ $meja->nomor_meja }}</h3>
                <p class="text-xs font-medium text-gray-400 mt-0.5">Kapasitas: {{ $meja->kapasitas ?? 4 }} Orang</p>
              </div>

              {{-- Status Badge --}}
              <span class="px-2.5 py-1 rounded-full text-xs font-bold border inline-flex items-center gap-1.5 shrink-0 {{ $meja->status === 'kosong' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                @if($meja->status === 'kosong')
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                  <span>Tersedia</span>
                @else
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                  <span>Terisi</span>
                @endif
              </span>
            </div>

            {{-- Card Middle: Order Details or Empty State --}}
            @if($meja->status === 'kosong' || !$openBillMeja)
              <div class="py-3 px-3 rounded-xl bg-gray-50/60 border border-gray-100 flex items-center justify-center text-center">
                <p class="text-xs text-gray-400 font-medium">Meja siap untuk pesanan baru</p>
              </div>
            @else
              @php
                $namaKonsumenCard = 'Tamu';
                $orderIdCard = 'DIN-' . ($openBillMeja->id ?? '-');
                $tagihanCard = 0;
                if ($openBillMeja) {
                    if (is_array($openBillMeja)) {
                        $namaKonsumenCard = $openBillMeja['nama_konsumen'] ?? (isset($openBillMeja['catatan']) ? explode('|', $openBillMeja['catatan'])[0] : 'Tamu');
                        $orderIdCard = $openBillMeja['id_pesanan'] ?? ('DIN-' . ($openBillMeja['id'] ?? '-'));
                        $tagihanCard = $openBillMeja['total_tagihan'] ?? 0;
                    } else {
                        $namaKonsumenCard = $openBillMeja->nama_konsumen ?? ($openBillMeja->catatan ? explode('|', $openBillMeja->catatan)[0] : 'Tamu');
                        $orderIdCard = $openBillMeja->id_pesanan ?? ('DIN-' . ($openBillMeja->id ?? '-'));
                        $tagihanCard = $openBillMeja->total_tagihan ?? 0;
                    }
                }
              @endphp
              <div class="p-3 rounded-xl bg-white border border-amber-200/70 shadow-2xs space-y-2 text-xs">
                <div class="flex items-center justify-between">
                  <span class="text-gray-400 font-medium">Pemesan:</span>
                  <span class="font-bold text-gray-800 truncate max-w-[150px]">
                    {{ $namaKonsumenCard }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-400 font-medium">Kode Pesanan:</span>
                  <span class="font-mono font-bold text-primary">
                    #{{ $orderIdCard }}
                  </span>
                </div>
                @if(!empty($tagihanCard))
                <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                  <span class="text-gray-400 font-medium">Tagihan:</span>
                  <span class="font-extrabold text-gray-900">Rp {{ number_format($tagihanCard, 0, ',', '.') }}</span>
                </div>
                @endif

                {{-- KOT Status from polling --}}
                <template x-if="mejaStatusMap[{{ $meja->id }}] && mejaStatusMap[{{ $meja->id }}].kot">
                  <div class="pt-1 flex items-center justify-between border-t border-gray-100">
                    <span class="text-gray-400 font-medium">Status Dapur:</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold border inline-flex items-center"
                          :class="getKotStatusClass(mejaStatusMap[{{ $meja->id }}].kot.status_tiket_dapur_id)">
                      <span x-text="getKotStatusLabel(mejaStatusMap[{{ $meja->id }}].kot.status_tiket_dapur_id)"></span>
                    </span>
                  </div>
                </template>
              </div>
            @endif
          </div>

          {{-- Card Footer Actions --}}
          <div class="p-3.5 pt-0 flex items-center gap-2">
            @if($meja->status === 'kosong')
              <button type="button" @click="selectTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                      class="w-full py-2.5 px-3 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center bg-gray-900 hover:bg-black text-white cursor-pointer active:scale-98">
                <span x-text="selectedTable == {{ $meja->id }} ? '✓ Meja Terpilih' : 'Pilih Meja & Pesan'"></span>
              </button>
            @else
              <div class="w-full grid grid-cols-2 gap-2">
                <a href="{{ route('pos.dinein.checkout', $meja->id) }}"
                   class="py-2.5 px-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-xs cursor-pointer text-center">
                  Bayar
                </a>

                <button type="button"
                        @click="confirmClearTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                        class="py-2.5 px-2 bg-gray-100 hover:bg-amber-50 hover:text-amber-800 active:scale-95 text-gray-700 font-bold rounded-xl text-xs transition-all border border-gray-200 shadow-2xs cursor-pointer text-center">
                  Kosongkan
                </button>
              </div>

              {{-- Hidden Clear Form --}}
              <form action="{{ route('pos.dinein.clear-table', $meja->id) }}" method="POST" id="form-clear-{{ $meja->id }}" class="hidden">
                @csrf
                @method('PATCH')
              </form>
            @endif
          </div>

        </div>
        @endforeach
      </div>

      {{-- ───────────────── MODE 2: TABLE LIST VIEW (TABEL RAPI ELEGAN) ───────────────── --}}
      <div x-show="mejaLayoutMode === 'table'" x-transition class="bg-white border border-gray-200/80 rounded-2xl shadow-2xs overflow-hidden">
        <x-ui.table class="min-w-[900px]">
          <x-ui.table.header>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-20">No</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Meja</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kapasitas</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status Meja</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pesanan Aktif</th>
            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status Dapur (KOT)</th>
            <th class="px-4 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi Kasir</th>
          </x-ui.table.header>
          <tbody class="divide-y divide-gray-100">
            @foreach($mejas as $index => $meja)
            @php
              $openBillMeja = $openBills->firstWhere('meja_id', $meja->id);
              $subStatusMeja = $openBillMeja->status_pesanan_id ?? 'kosong';
            @endphp
            <x-ui.table.row x-show="(tableFilter === 'semua' || (tableFilter === 'kosong' && '{{ $meja->status }}' === 'kosong') || (tableFilter === 'terisi' && '{{ $meja->status }}' !== 'kosong') || (tableFilter === '{{ $subStatusMeja }}')) && (!tableSearch || '{{ strtolower(addslashes($meja->nomor_meja)) }}'.includes(tableSearch.toLowerCase()))"
                            class="hover:bg-gray-50/80 transition-colors {{ $meja->status !== 'kosong' ? 'bg-amber-50/15' : '' }}">
              
              {{-- No --}}
              <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $index + 1 }}</td>

              {{-- Nomor Meja --}}
              <td class="px-4 py-4 align-middle">
                <span class="text-sm font-bold text-gray-900 block leading-tight">{{ $meja->nomor_meja }}</span>
              </td>

              {{-- Kapasitas --}}
              <td class="px-4 py-4 text-sm text-gray-600 font-medium align-middle">
                {{ $meja->kapasitas ?? 4 }} Orang
              </td>
              
              {{-- Status Meja --}}
              <td class="px-4 py-4 align-middle">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold border inline-flex items-center gap-1.5 {{ $meja->status === 'kosong' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/70' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                  @if($meja->status === 'kosong')
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Tersedia</span>
                  @else
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Terisi</span>
                  @endif
                </span>
              </td>

              {{-- Pesanan Aktif --}}
              <td class="px-4 py-4 align-middle">
                @if($meja->status !== 'kosong' && $openBillMeja)
                  @php
                    $namaKonsumenTable = 'Tamu';
                    $orderIdTable = 'DIN-' . ($openBillMeja->id ?? '-');
                    $tagihanTable = 0;
                    if (is_array($openBillMeja)) {
                        $namaKonsumenTable = $openBillMeja['nama_konsumen'] ?? (isset($openBillMeja['catatan']) ? explode('|', $openBillMeja['catatan'])[0] : 'Tamu');
                        $orderIdTable = $openBillMeja['id_pesanan'] ?? ('DIN-' . ($openBillMeja['id'] ?? '-'));
                        $tagihanTable = $openBillMeja['total_tagihan'] ?? 0;
                    } else {
                        $namaKonsumenTable = $openBillMeja->nama_konsumen ?? ($openBillMeja->catatan ? explode('|', $openBillMeja->catatan)[0] : 'Tamu');
                        $orderIdTable = $openBillMeja->id_pesanan ?? ('DIN-' . ($openBillMeja->id ?? '-'));
                        $tagihanTable = $openBillMeja->total_tagihan ?? 0;
                    }
                  @endphp
                  <div class="space-y-0.5">
                    <p class="text-xs font-bold text-gray-900">
                      {{ $namaKonsumenTable }}
                    </p>
                    <p class="text-[11px] font-mono text-primary font-bold">
                      #{{ $orderIdTable }}
                      @if(!empty($tagihanTable))
                        • Rp {{ number_format($tagihanTable, 0, ',', '.') }}
                      @endif
                    </p>
                  </div>
                @else
                  <span class="text-xs text-gray-400 font-medium">-</span>
                @endif
              </td>

              {{-- Status KOT Dapur --}}
              <td class="px-4 py-4 align-middle">
                <template x-if="mejaStatusMap[{{ $meja->id }}] && mejaStatusMap[{{ $meja->id }}].kot">
                  <span class="px-2.5 py-1 rounded-lg text-xs font-bold border inline-flex items-center gap-1"
                        :class="getKotStatusClass(mejaStatusMap[{{ $meja->id }}].kot.status_tiket_dapur_id)">
                    <span x-text="getKotStatusLabel(mejaStatusMap[{{ $meja->id }}].kot.status_tiket_dapur_id)"></span>
                    <span class="font-mono text-xs bg-white/50 px-1.5 rounded" x-text="mejaStatusMap[{{ $meja->id }}].kot.nomor_tiket"></span>
                  </span>
                </template>
                <template x-if="mejaStatusMap[{{ $meja->id }}] && !mejaStatusMap[{{ $meja->id }}].kot && mejaStatusMap[{{ $meja->id }}].has_active_order">
                  <span class="px-2.5 py-1 rounded-lg text-xs font-bold border inline-flex items-center gap-1 bg-primary-soft text-primary border-primary/20">
                    KOT Menunggu
                  </span>
                </template>
                <template x-if="!mejaStatusMap[{{ $meja->id }}] || (!mejaStatusMap[{{ $meja->id }}].kot && !mejaStatusMap[{{ $meja->id }}].has_active_order)">
                  <span class="text-xs text-gray-400 font-medium">-</span>
                </template>
              </td>
              
              {{-- Aksi Kasir --}}
              <td class="px-4 py-4 text-right align-middle">
                @if($meja->status === 'kosong')
                  <button type="button" @click="selectTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                          class="px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-xs inline-flex items-center justify-center bg-gray-900 hover:bg-black text-white cursor-pointer active:scale-95">
                    <span x-text="selectedTable == {{ $meja->id }} ? '✓ Terpilih' : 'Pilih Meja'"></span>
                  </button>
                @else
                    <div class="inline-flex items-center justify-end gap-1.5">
                      <a href="{{ route('pos.dinein.checkout', $meja->id) }}"
                         class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-xs cursor-pointer">
                        Bayar
                      </a>

                      <button type="button" 
                              @click="confirmClearTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                              class="px-3 py-2 rounded-xl text-xs font-bold text-gray-700 bg-gray-100 hover:bg-amber-50 hover:text-amber-800 border border-gray-200 transition-colors inline-flex items-center justify-center shadow-2xs cursor-pointer">
                        Kosongkan
                      </button>
                    </div>
                  @endif
                </div>
              </td>
            </x-ui.table.row>
            @endforeach
          </tbody>
        </x-ui.table>
      </div>

    </div>

    {{-- ══════════════════════  VIEW 3 · PESANAN BELUM DIBAYAR  ════════════════════════ --}}
    <div x-show="leftView === 'open_bills'" x-cloak class="flex-1 overflow-y-auto p-4 md:p-6 pb-8 bg-secondary-soft">

      {{-- Empty state --}}
      <template x-if="openBills.length === 0">
        <div class="bg-white rounded-xl border border-gray-200/80 p-12 text-center shadow-xs max-w-md mx-auto my-8">
          <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-2xl">
            <x-heroicon-o-check-circle class="w-5 h-5" />
          </div>
          <h3 class="text-base font-extrabold text-gray-900">Tidak Ada Transaksi</h3>
          <p class="text-xs text-gray-500 mt-1 leading-relaxed">Belum ada pesanan dine in yang tercatat.</p>
        </div>
      </template>

      {{-- List Open Bills Table --}}
      <div x-show="openBills.length > 0" class="pb-[150px]">
        <x-ui.table class="min-w-[1000px]">
          <x-ui.table.header>
            <th class="px-4 py-3.5 text-center w-12">No</th>
            <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
            <th class="px-4 py-3.5 text-left">Kode Pesanan</th>
            <th class="px-4 py-3.5 text-left">Meja</th>
            <th class="px-4 py-3.5 text-left">Pelanggan</th>
            <th class="px-4 py-3.5 text-left">Metode Pemesanan</th>
            <th class="px-4 py-3.5 text-right">Total</th>
            <th class="px-4 py-3.5 text-center">Status Pesanan</th>
            <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
            <th class="px-4 py-3.5 text-right">Aksi</th>
          </x-ui.table.header>
          <tbody class="divide-y divide-gray-100">
            <template x-for="(bill, index) in openBills.filter(b => {
                if (openBillFilter === 'pos' && b.sumber_pesanan === 'self_order') return false;
                if (openBillFilter === 'qr' && b.sumber_pesanan !== 'self_order') return false;
                if (openBillStatusFilter !== 'semua') {
                    const target = openBillStatusFilter.toLowerCase();
                    const bStatus = String(b.status || '').toLowerCase();
                    const bRaw = String(b.status_raw || '').toLowerCase();
                    if (bStatus !== target && bRaw !== target) return false;
                }
                if (openBillSearch) {
                    const query = openBillSearch.toLowerCase();
                    const noPesanan = String(b.id_pesanan || ('din-' + b.id)).toLowerCase();
                    const pelanggan = String(b.nama_konsumen || '').toLowerCase();
                    if (!noPesanan.includes(query) && !pelanggan.includes(query)) return false;
                }
                return true;
              })" :key="bill.id">
              <x-ui.table.row x-bind:class="bill.is_new ? 'bg-amber-50/50' : ''">
                
                {{-- No --}}
                <td class="px-4 py-4 text-center font-bold text-gray-500 text-sm align-middle" x-text="index + 1"></td>
                
                {{-- Tanggal Pesan --}}
                <td class="px-4 py-4 align-middle whitespace-nowrap text-sm text-gray-700">
                  <span x-text="bill.dibuat_pada ? (new Date(bill.dibuat_pada).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'}) + ', ' + new Date(bill.dibuat_pada).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}).replace(':', '.') + ' WIB') : '-'"></span>
                </td>

                {{-- Kode Pesanan --}}
                <td class="px-4 py-4 align-middle">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-mono text-slate-900 font-bold" x-text="bill.id_pesanan || ('DIN-' + bill.id)"></span>
                    <template x-if="bill.is_new || bill.is_new_order">
                      <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-red-500 text-white animate-pulse">BARU</span>
                    </template>
                  </div>
                </td>
                
                {{-- Meja --}}
                <td class="px-4 py-4 align-middle">
                  <span class="text-sm font-semibold text-slate-700" x-text="bill.meja ? (bill.meja.nomor_meja.startsWith('Meja') ? bill.meja.nomor_meja : 'Meja ' + bill.meja.nomor_meja) : '-'"></span>
                </td>

                {{-- Customer --}}
                <td class="px-4 py-4 align-middle">
                  <span class="font-medium text-sm text-slate-900" x-text="(bill.nama_konsumen || 'Tamu').split(' – ')[0].split(' - ')[0].split(' | ')[0].replace(/\(\d+\s*tamu\)/i, '').trim()"></span>
                </td>

                {{-- Metode Pemesanan --}}
                <td class="px-4 py-4 align-middle whitespace-nowrap">
                  <span class="text-xs font-semibold px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5"
                        :class="bill.sumber_pesanan === 'self_order' ? 'bg-purple-50 text-purple-700 border border-purple-200/90' : 'bg-emerald-50 text-emerald-800 border border-emerald-200/90'"
                        x-text="bill.sumber_pesanan === 'self_order' ? 'Self-order' : 'Pemesanan via Kasir'"></span>
                </td>
                
                {{-- Total Tagihan --}}
                <td class="px-4 py-4 text-right align-middle font-bold text-gray-900 tabular-nums whitespace-nowrap">
                  <span x-text="'Rp ' + formatPrice(bill.total_tagihan || (bill.items || []).reduce((s, i) => s + (i.subtotal || ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * (i.qty || 0))), 0))"></span>
                </td>
                
                {{-- Status Pesanan (Read-only Badge) --}}
                <td class="px-4 py-4 text-center align-middle">
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold shadow-2xs whitespace-nowrap"
                        :class="getStatusClass(getStatusId(bill))">
                    <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDot(getStatusId(bill))"></span>
                    <span x-text="getStatusLabel(getStatusId(bill))"></span>
                  </span>
                </td>
                
                {{-- Pembayaran --}}
                <td class="px-4 py-4 text-center align-middle">
                  <span class="inline-flex items-center gap-1.5 font-medium rounded-full border text-xs px-2 py-0.5"
                        :class="bill.status_raw === 'selesai' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/50' : (bill.status_raw === 'dibatalkan' ? 'bg-red-50 text-red-700 border-red-200/50' : 'bg-orange-50 text-orange-700 border-orange-200/50')"
                        x-text="bill.status_raw === 'selesai' ? 'Lunas' : (bill.status_raw === 'dibatalkan' ? 'Dibatalkan' : 'Belum Bayar')">
                  </span>
                </td>
                
                {{-- Action Buttons --}}
                <td class="px-4 py-4 text-right align-middle">
                  <div class="flex items-center justify-end gap-1.5">
                    <template x-if="bill.status_raw === 'aktif'">
                      <div class="flex items-center justify-end gap-1.5">
                        <!-- Detail Pesanan -->
                        <button type="button" @click="openDetailModal(bill)"
                                title="Detail Pesanan"
                                class="flex items-center justify-center p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs cursor-pointer">
                          <x-heroicon-o-eye class="w-4 h-4 pointer-events-none" />
                        </button>

                        <!-- Cetak Checker (Modal Pratinjau) -->
                        <button type="button" @click="openCheckerPreview(bill)"
                                title="Cetak Checker"
                                class="flex items-center justify-center p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs cursor-pointer">
                          <x-heroicon-o-printer class="w-4 h-4 pointer-events-none" />
                        </button>

                        <!-- Konfirmasi - khusus jika pesanan belum dikonfirmasi (status 1) -->
                        <template x-if="Number(bill.status_pesanan_id) === 1 || bill.status === 'Menunggu Konfirmasi' || getStatusId(bill) === 1">
                          <button type="button" @click="konfirmasiPesanan(bill)"
                                  class="px-4 py-2 bg-primary hover:bg-primary-container active:scale-[0.99] text-white rounded-xl text-xs font-black transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <x-heroicon-o-check-circle class="w-3.5 h-3.5 pointer-events-none" />
                            <span>KONFIRMASI</span>
                          </button>
                        </template>

                        <!-- Hidangkan - khusus jika pesanan siap disajikan dari dapur (status 4) -->
                        <template x-if="Number(bill.status_pesanan_id) === 4 || bill.status === 'Pesanan Siap' || getStatusId(bill) === 4">
                          <button type="button" @click="hidangkanPesanan(bill)"
                                  class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 active:scale-[0.99] text-white rounded-xl text-xs font-black transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
                                  title="Tandai Telah Dihidangkan">
                            <x-heroicon-o-check-badge class="w-3.5 h-3.5 pointer-events-none" />
                            <span>HIDANGKAN</span>
                          </button>
                        </template>

                        @if(!$isPelayan)
                        <!-- BAYAR -->
                        <button type="button" @click="proceedToCheckout(bill)"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl text-xs font-black transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                          <span>BAYAR</span>
                          <x-heroicon-o-chevron-right class="w-3.5 h-3.5 pointer-events-none" />
                        </button>
                        @endif
                      </div>
                    </template>
                    <template x-if="bill.status_raw !== 'aktif'">
                      <div class="flex items-center justify-end gap-1.5">
                        <a :href="'/pos/dinein/receipts/' + bill.id" target="_blank"
                           title="Struk Nota"
                           class="flex items-center justify-center p-2 bg-slate-100 hover:bg-primary-container hover:text-white text-slate-700 rounded-xl transition-all shadow-xs cursor-pointer">
                          <x-heroicon-o-printer class="w-4 h-4" />
                        </a>
                        <button type="button" @click="openDetailModal(bill)"
                                title="Detail Transaksi"
                                class="flex items-center justify-center p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs cursor-pointer">
                          <x-heroicon-o-eye class="w-4 h-4 pointer-events-none" />
                        </button>
                        @if(!$isPelayan)
                        <template x-if="bill.status_raw === 'selesai'">
                          <button type="button" @click="openVoidModal(bill)"
                                  title="Void Transaksi"
                                  class="flex items-center justify-center p-2 bg-red-50 hover:bg-red-600 hover:text-white text-red-700 rounded-xl transition-all shadow-xs">
                            <x-heroicon-o-no-symbol class="w-4 h-4" />
                          </button>
                        </template>
                        @endif
                      </div>
                    </template>
                  </div>
                </td>
              </x-ui.table.row>
            </template>
          </tbody>
        </x-ui.table>
      </div>

    </div>

  </div>

  {{-- ─────────────────────────────── RIGHT PANEL: CART ────────────────────────────── --}}
  <div x-show="leftView === 'menu'" class="w-full lg:w-[380px] xl:w-[420px] bg-white lg:border-l border-t lg:border-t-0 border-gray-200/80 flex flex-col justify-between shrink-0 shadow-xs">
    
    <div class="flex flex-col h-full justify-between">
      {{-- Header & Customer Input --}}
      <div class="p-5 border-b border-gray-100 space-y-3 shrink-0">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-extrabold text-primary">Detail Pesanan</h2>
        </div>

        <div class="space-y-2 pt-1">
          {{-- Input 1: Pilih Meja (Custom Dropdown) --}}
          <div class="relative" @click.outside="showTableDropdown = false">
            <button type="button" @click.stop="showTableDropdown = !showTableDropdown"
                    class="w-full h-10 px-3.5 text-xs font-medium rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10 outline-none transition text-left flex items-center justify-between cursor-pointer">
              <span :class="selectedTable ? 'text-gray-900 font-extrabold' : 'text-gray-400'"
                    x-text="selectedTableLabel ? (selectedTableLabel.startsWith('Meja') ? selectedTableLabel : 'Meja ' + selectedTableLabel) : 'Pilih Meja Resto *'"></span>
              <x-heroicon-o-chevron-down class="w-3 h-3 text-gray-400 transition-transform" x-bind:class="showTableDropdown ? 'rotate-180' : ''" />
            </button>

            {{-- Custom Dropdown Panel --}}
            <div x-show="showTableDropdown" x-transition.opacity style="display: none;"
                 class="absolute left-0 right-0 top-11 bg-white rounded-xl border border-gray-200/90 shadow-2xl z-50 p-2 space-y-1 max-h-60 overflow-y-auto">
              
              <div class="px-2 py-1 flex items-center justify-between border-b border-gray-100 mb-1">
                <span class="text-xs font-extrabold text-gray-400 uppercase tracking-wider">Pilih Meja Resto</span>
                <button type="button" @click.stop="leftView = 'meja'; showTableDropdown = false" class="text-sm font-bold text-emerald-800 hover:underline">
                  Denah Meja ➔
                </button>
              </div>

              <template x-for="m in mejasList" :key="m.id">
                <button type="button"
                        @click.stop="if (m.status === 'kosong') { selectTable(m.id, m.nomor_meja); showTableDropdown = false }"
                        :disabled="m.status !== 'kosong'"
                        :class="m.status !== 'kosong' ? 'opacity-40 bg-gray-100/70 text-gray-400 cursor-not-allowed border-transparent' : (selectedTable == m.id ? 'bg-emerald-50 text-primary font-black border-emerald-200 cursor-pointer' : 'hover:bg-gray-50 text-gray-700 font-bold border-transparent cursor-pointer')"
                        class="w-full text-left px-3 py-2 text-xs rounded-xl border flex items-center justify-between transition-all">
                  <span x-text="m.nomor_meja.startsWith('Meja') ? m.nomor_meja : 'Meja ' + m.nomor_meja"></span>
                  <span class="text-xs px-2 py-0.5 rounded-full font-bold"
                        :class="m.status === 'kosong' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900 border border-amber-200'"
                        x-text="m.status === 'kosong' ? 'Kosong' : 'Terisi (Penuh)'"></span>
                </button>
              </template>
            </div>
          </div>

          {{-- Input 2: Nama Pelanggan --}}
          <input type="text" x-model="customerName" placeholder="Nama Pelanggan / Konsumen *"
                 class="w-full h-10 px-3.5 text-sm font-medium rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10 outline-none transition">

          {{-- Input 3: No Telepon / WhatsApp --}}
          <input type="text" x-model="customerPhone" placeholder="No. Telepon / WhatsApp (Opsional)"
                 inputmode="numeric" pattern="[0-9]*" maxlength="13"
                 oninput="let v = this.value.replace(/[^0-9]/g, ''); if(v.startsWith('62')) v = '0' + v.substring(2); if(v.length > 0 && v[0] !== '0') v = '0' + v; if(v.length > 1 && v[1] !== '8') v = '08' + v.substring(1); this.value = v; customerPhone = v"
                 class="w-full h-10 px-3.5 text-sm font-medium rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/10 outline-none transition">
        </div>
      </div>

      {{-- Cart Items List --}}
      <div class="flex-1 overflow-y-auto p-4 space-y-3 divide-y divide-gray-100">
        <template x-if="cart.length === 0">
          <div class="h-full flex flex-col items-center justify-center py-12 text-center text-gray-400 space-y-2">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-xl">
              <x-heroicon-o-shopping-bag class="w-5 h-5" />
            </div>
            <p class="text-xs font-bold">Keranjang Masih Kosong</p>
            <p class="text-xs text-gray-400 max-w-[200px]">Pilih menu makanan / minuman dari katalog di sebelah kiri.</p>
          </div>
        </template>

        <template x-for="(item, index) in cart" :key="item.menu_id">
          <div class="pt-3 first:pt-0 space-y-1.5">
            <div class="flex items-start justify-between gap-2">
              <span class="font-extrabold text-sm text-gray-900 flex-1 leading-snug" x-text="item.nama"></span>
              <span class="font-black text-sm text-primary" x-text="'Rp ' + formatPrice(item.harga * item.qty)"></span>
            </div>

            <div class="flex items-center justify-between pt-1">
              <input type="text" x-model="item.catatan" placeholder="Catatan khusus…"
                     class="h-7 text-sm px-2.5 rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-primary outline-none w-44">

              <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl">
                <button @click="updateQty(index, -1)" class="w-6 h-6 rounded-full bg-white text-gray-700 font-extrabold text-sm flex items-center justify-center hover:bg-gray-200 transition-colors">-</button>
                <span class="w-6 text-center text-xs font-black text-gray-900" x-text="item.qty"></span>
                <button @click="addToCart(item.menu_id, item.nama, item.harga)" class="w-6 h-6 rounded-full bg-white text-gray-700 font-extrabold text-sm flex items-center justify-center hover:bg-gray-200 transition-colors">+</button>
              </div>
            </div>
          </div>
        </template>
      </div>

      {{-- Bottom Summary & Action Bar --}}
      <div class="p-5 border-t border-gray-200/80 bg-gray-50/60 space-y-3 shrink-0">
        <div class="space-y-1 text-sm font-medium text-gray-500">
          <div class="flex justify-between">
            <span>Total Item</span>
            <span class="font-bold text-gray-900" x-text="totalQty + ' Item'"></span>
          </div>
          <div class="flex justify-between text-sm pt-1 pb-1">
            <span class="font-bold text-gray-700">Subtotal Item</span>
            <span class="font-bold text-gray-700" x-text="'Rp ' + formatPrice(subTotal)"></span>
          </div>
          <template x-if="layananAktif && subTotal > 0">
            <div class="flex justify-between text-sm pb-1">
              <span class="font-bold text-gray-700">Biaya Layanan</span>
              <span class="font-bold text-gray-700" x-text="'Rp ' + formatPrice(totalServiceFee)"></span>
            </div>
          </template>
          <template x-if="pajakAktif && persentasePajak > 0">
            <div class="flex justify-between text-sm pb-2">
              <span class="font-bold text-gray-700" x-text="`Pajak (${persentasePajak}%)`"></span>
              <span class="font-bold text-gray-700" x-text="'Rp ' + formatPrice(totalPajak)"></span>
            </div>
          </template>
          <div class="flex justify-between text-sm pt-1 border-t border-gray-200/60">
            <span class="font-bold text-gray-700">Total Tagihan</span>
            <span class="font-black text-base text-primary" x-text="'Rp ' + formatPrice(totalPrice)"></span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-1">
          <button type="button" @click.stop="submitOrder('simpan')" :disabled="isSubmitting"
                  class="py-3.5 px-3 rounded-xl border border-gray-300 bg-white hover:bg-gray-100 text-gray-800 font-extrabold text-xs transition-all shadow-2xs cursor-pointer">
            SIMPAN
          </button>
          <button type="button" @click.stop="submitOrder('bayar')" :disabled="isSubmitting"
                  class="py-3.5 px-3 rounded-xl bg-primary hover:bg-primary-container active:scale-[0.99] text-white font-extrabold text-xs transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer">
            <span>BAYAR</span>
            <x-heroicon-o-chevron-right class="w-3 h-3" />
          </button>
        </div>
      </div>
    </div>

  </div>

  {{-- ── MODAL DETAIL PESANAN DINE-IN ── --}}
  <div x-show="showTrxDetailModal"
       x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95"
       x-transition:enter-end="opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100"
       x-transition:leave-end="opacity-0 scale-95"
       class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4"
       style="display: none;">
    <div class="bg-white rounded-2xl w-full max-w-xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh]"
         @click.outside="showTrxDetailModal = false">

      {{-- Modal Header --}}
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0 bg-white">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-primary-soft text-primary flex items-center justify-center font-bold text-base">
            <x-heroicon-o-document-text class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-base font-extrabold text-gray-900 leading-tight">Detail Pesanan Dine-In</h3>
            <p class="text-xs text-gray-400 font-medium" x-text="selectedTrxDetail ? (selectedTrxDetail.id_pesanan || ('DIN-' + selectedTrxDetail.id)) : ''"></p>
          </div>
        </div>
        <button type="button" @click="showTrxDetailModal = false"
                class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer">
          <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
      </div>

      {{-- Modal Body (scrollable) --}}
      <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5" x-show="selectedTrxDetail">

        {{-- 1. INFORMASI PESANAN --}}
        <div class="space-y-2.5">
          <div class="flex items-center gap-2">
            <h4 class="text-xs font-black uppercase tracking-wider text-primary">Informasi Pesanan</h4>
            <div class="h-px bg-gray-200 flex-1"></div>
          </div>
          <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5 text-xs">
            <div class="flex items-baseline justify-between sm:justify-start gap-2">
              <span class="text-gray-500 min-w-[110px]">Kode Pesanan</span>
              <span class="text-gray-400 sm:inline hidden">:</span>
              <span class="font-bold text-gray-900 font-mono" x-text="selectedTrxDetail ? (selectedTrxDetail.id_pesanan || ('DIN-' + selectedTrxDetail.id)) : '-'"></span>
            </div>
            <div class="flex items-baseline justify-between sm:justify-start gap-2">
              <span class="text-gray-500 min-w-[110px]">Tanggal</span>
              <span class="text-gray-400 sm:inline hidden">:</span>
              <span class="font-medium text-gray-800" x-text="selectedTrxDetail ? formatDateStr(selectedTrxDetail.dibuat_pada || selectedTrxDetail.created_at, true) : '-'"></span>
            </div>
            <div class="flex items-baseline justify-between sm:justify-start gap-2">
              <span class="text-gray-500 min-w-[110px]">Meja</span>
              <span class="text-gray-400 sm:inline hidden">:</span>
              <span class="font-bold text-gray-900" x-text="selectedTrxDetail?.meja ? (typeof selectedTrxDetail.meja === 'object' ? (selectedTrxDetail.meja.nomor_meja?.startsWith('Meja') ? selectedTrxDetail.meja.nomor_meja : 'Meja ' + selectedTrxDetail.meja.nomor_meja) : (String(selectedTrxDetail.meja).startsWith('Meja') ? selectedTrxDetail.meja : 'Meja ' + selectedTrxDetail.meja)) : '-'"></span>
            </div>
            <div class="flex items-baseline justify-between sm:justify-start gap-2">
              <span class="text-gray-500 min-w-[110px]">Nama Pelanggan</span>
              <span class="text-gray-400 sm:inline hidden">:</span>
              <span class="font-bold text-gray-900" x-text="(selectedTrxDetail?.nama_konsumen || 'Tamu').split(' – ')[0].split(' - ')[0].split(' | ')[0].replace(/\(\d+\s*tamu\)/i, '').trim()"></span>
            </div>
            <div class="flex items-baseline justify-between sm:justify-start gap-2">
              <span class="text-gray-500 min-w-[110px]">No. Telepon</span>
              <span class="text-gray-400 sm:inline hidden">:</span>
              <span class="font-medium text-gray-800" x-text="selectedTrxDetail?.no_telepon || '-'"></span>
            </div>
            <div class="flex items-baseline justify-between sm:justify-start gap-2">
              <span class="text-gray-500 min-w-[110px]">Metode Pemesanan</span>
              <span class="text-gray-400 sm:inline hidden">:</span>
              <span class="font-bold"
                    :class="selectedTrxDetail?.sumber_pesanan === 'self_order' ? 'text-purple-700' : 'text-emerald-700'"
                    x-text="selectedTrxDetail?.metode_pemesanan || (selectedTrxDetail?.sumber_pesanan === 'self_order' ? 'Self-order' : 'Pemesanan via Kasir')"></span>
            </div>
          </div>
        </div>

        {{-- 2. RINCIAN PESANAN --}}
        <div class="space-y-2.5">
          <div class="flex items-center gap-2">
            <h4 class="text-xs font-black uppercase tracking-wider text-primary">Rincian Pesanan</h4>
            <div class="h-px bg-gray-200 flex-1"></div>
          </div>
          
          <div class="border border-gray-200 rounded-xl overflow-hidden shadow-2xs">
            <table class="w-full text-left text-xs border-collapse">
              <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold">
                <tr>
                  <th class="px-4 py-2.5">Menu</th>
                  <th class="px-3 py-2.5 text-center w-14">Qty</th>
                  <th class="px-4 py-2.5 text-right w-28">Harga</th>
                  <th class="px-4 py-2.5 text-right w-28">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <template x-for="item in (selectedTrxDetail?.items || [])" :key="'item-' + item.id">
                  <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-2.5 align-top">
                      <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-gray-900" x-text="item.menu ? (item.menu.nama_menu || item.menu.nama) : (item.nama_menu || 'Menu')"></span>
                        <template x-if="item.is_tambahan">
                          <span class="px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 border border-amber-200/60 text-[9px] font-bold uppercase">Tambahan</span>
                        </template>
                      </div>
                      <template x-if="item.catatan">
                        <p class="text-[11px] text-amber-700 italic mt-0.5" x-text="'Catatan: ' + item.catatan"></p>
                      </template>
                    </td>
                    <td class="px-3 py-2.5 text-center font-bold text-gray-700 align-top" x-text="item.qty || item.jumlah"></td>
                    <td class="px-4 py-2.5 text-right font-medium text-gray-600 align-top whitespace-nowrap" x-text="'Rp ' + formatPrice(item.harga_satuan || (item.menu ? (item.menu.harga_jual || item.menu.harga) : 0))"></td>
                    <td class="px-4 py-2.5 text-right font-bold text-gray-900 align-top whitespace-nowrap" x-text="'Rp ' + formatPrice(item.subtotal || ((item.harga_satuan || (item.menu ? (item.menu.harga_jual || item.menu.harga) : 0)) * (item.qty || item.jumlah)))"></td>
                  </tr>
                </template>
                <template x-if="(selectedTrxDetail?.items || []).length === 0">
                  <tr>
                    <td colspan="4" class="p-4 text-center text-gray-400 italic">Tidak ada rincian item pesanan</td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          {{-- Subtotal, Layanan & Total Summary --}}
          <div class="bg-gray-50/70 rounded-xl p-3.5 border border-gray-100 space-y-1.5 text-xs">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal</span>
              <span class="font-medium text-gray-900" x-text="'Rp ' + formatPrice(selectedTrxDetail?.jumlah_sebelum_potongan || (selectedTrxDetail?.items || []).reduce((s, i) => s + (i.subtotal || ((i.harga_satuan || (i.menu ? (i.menu.harga_jual || i.menu.harga) : 0)) * (i.qty || i.jumlah))), 0))"></span>
            </div>
            <template x-if="selectedTrxDetail && (selectedTrxDetail.biaya_pelayanan > 0 || selectedTrxDetail.nominal_biaya_layanan > 0)">
              <div class="flex justify-between text-gray-600">
                <span>Biaya Layanan</span>
                <span class="font-medium text-gray-900" x-text="'Rp ' + formatPrice(selectedTrxDetail.biaya_pelayanan || selectedTrxDetail.nominal_biaya_layanan)"></span>
              </div>
            </template>
            <div class="flex justify-between font-black text-gray-900 text-sm pt-2 border-t border-gray-200">
              <span class="text-primary">Total Pesanan</span>
              <span class="text-primary" x-text="'Rp ' + formatPrice(selectedTrxDetail?.total_tagihan || (selectedTrxDetail?.items || []).reduce((s, i) => s + (i.subtotal || ((i.harga_satuan || (i.menu ? (i.menu.harga_jual || i.menu.harga) : 0)) * (i.qty || i.jumlah))), 0))"></span>
            </div>
          </div>
        </div>

        {{-- 3. STATUS --}}
        <div class="space-y-2.5">
          <div class="flex items-center gap-2">
            <h4 class="text-xs font-black uppercase tracking-wider text-primary">Status</h4>
            <div class="h-px bg-gray-200 flex-1"></div>
          </div>
          <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div class="space-y-1">
              <span class="text-gray-500 block">Status Pesanan:</span>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-bold shadow-2xs"
                    :class="{
                      1: 'bg-amber-50 text-amber-800 border-amber-200/90',
                      2: 'bg-blue-50 text-blue-800 border-blue-200/90',
                      3: 'bg-indigo-50 text-indigo-800 border-indigo-200/90',
                      4: 'bg-purple-50 text-purple-800 border-purple-200/90',
                      8: 'bg-teal-50 text-teal-800 border-teal-200/90',
                      5: 'bg-emerald-50 text-emerald-800 border-emerald-200/90',
                      6: 'bg-rose-50 text-rose-800 border-rose-200/90'
                    }[selectedTrxDetail?.status_pesanan_id || (selectedTrxDetail?.status_raw === 'selesai' ? 5 : (selectedTrxDetail?.status_raw === 'dibatalkan' ? 6 : 2))] || 'bg-gray-50 text-gray-700 border-gray-200'">
                <span class="w-1.5 h-1.5 rounded-full"
                      :class="{
                        1: 'bg-amber-500',
                        2: 'bg-blue-500',
                        3: 'bg-indigo-500 animate-pulse',
                        4: 'bg-purple-500',
                        8: 'bg-teal-500',
                        5: 'bg-emerald-500',
                        6: 'bg-rose-500'
                      }[selectedTrxDetail?.status_pesanan_id || (selectedTrxDetail?.status_raw === 'selesai' ? 5 : (selectedTrxDetail?.status_raw === 'dibatalkan' ? 6 : 2))] || 'bg-gray-400'"></span>
                <span x-text="{
                  1: 'Menunggu Konfirmasi',
                  2: 'Dikonfirmasi',
                  3: 'Sedang Diproses',
                  4: 'Pesanan Siap',
                  8: 'Pesanan Telah Dihidangkan',
                  5: 'Selesai',
                  6: 'Dibatalkan'
                }[selectedTrxDetail?.status_pesanan_id || (selectedTrxDetail?.status_raw === 'selesai' ? 5 : (selectedTrxDetail?.status_raw === 'dibatalkan' ? 6 : 2))] || (selectedTrxDetail?.status || 'Sedang Diproses')"></span>
              </span>
            </div>

            <div class="space-y-1">
              <span class="text-gray-500 block">Status Pembayaran:</span>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-bold shadow-2xs"
                    :class="selectedTrxDetail?.status_raw === 'selesai' ? 'bg-emerald-50 text-emerald-800 border-emerald-200/90' : (selectedTrxDetail?.status_raw === 'dibatalkan' ? 'bg-rose-50 text-rose-800 border-rose-200/90' : 'bg-orange-50 text-orange-800 border-orange-200/90')"
                    x-text="selectedTrxDetail?.status_raw === 'selesai' ? 'Lunas' : (selectedTrxDetail?.status_raw === 'dibatalkan' ? 'Dibatalkan' : 'Belum Bayar')">
              </span>
            </div>

            <div class="sm:col-span-2 pt-1 flex items-center gap-2">
              <span class="text-gray-500">Metode Pembayaran:</span>
              <span class="font-bold text-gray-900"
                    x-text="selectedTrxDetail?.status_raw === 'selesai' ? (selectedTrxDetail?.metode_bayar || selectedTrxDetail?.pembayaran?.[0]?.metode_pembayaran || 'Cash') : 'Belum dipilih'"></span>
            </div>
          </div>
        </div>

      </div>

      {{-- Modal Footer (Action Buttons) --}}
      <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-3 shrink-0 bg-gray-50/50">
        
        {{-- Jika Belum Dibayar: [ Cetak Bukti Pesanan ] [ Cetak Struk Dapur Checker ] [ Cetak Meja Checker ] [ Bayar ] --}}
        <template x-if="selectedTrxDetail && selectedTrxDetail.status_raw !== 'selesai'">
          <div class="flex items-center justify-between w-full gap-2 flex-wrap">
            <div class="flex items-center gap-1.5 flex-wrap">
              <button type="button"
                      @click="printReceiptPopup('/pos/dinein/pesanan/' + selectedTrxDetail.id + '/print-bukti', 'PrintBukti_' + selectedTrxDetail.id)"
                      class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer active:scale-95"
                      title="Cetak Bukti Pesanan untuk Konsumen">
                <x-heroicon-o-document-text class="w-3.5 h-3.5 text-amber-600" />
                <span>Cetak Bukti Pesanan</span>
              </button>
              <button type="button"
                      @click="printReceiptPopup('/pos/dinein/pesanan/' + selectedTrxDetail.id + '/print-dapur', 'PrintDapur_' + selectedTrxDetail.id)"
                      class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer active:scale-95"
                      title="Cetak Struk Dapur Checker untuk Tim Dapur">
                <x-heroicon-o-printer class="w-3.5 h-3.5 text-gray-500" />
                <span>Cetak Struk Dapur Checker</span>
              </button>
              <button type="button"
                      @click="printReceiptPopup('/pos/dinein/pesanan/' + selectedTrxDetail.id + '/print-meja', 'PrintMeja_' + selectedTrxDetail.id)"
                      class="inline-flex items-center gap-1 px-3 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer active:scale-95"
                      title="Cetak Meja Checker untuk Penanda Meja">
                <x-heroicon-o-clipboard-document-check class="w-3.5 h-3.5 text-emerald-600" />
                <span>Cetak Meja Checker</span>
              </button>
            </div>
            <div class="flex items-center gap-2">
              <template x-if="selectedTrxDetail.status_pesanan_id === 4 || selectedTrxDetail.status === 'Pesanan Siap'">
                <button type="button"
                        @click="hidangkanPesanan(selectedTrxDetail); showTrxDetailModal = false;"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-extrabold rounded-xl text-xs transition shadow-sm cursor-pointer active:scale-95">
                  <x-heroicon-o-check-badge class="w-3.5 h-3.5" />
                  <span>Tandai Telah Dihidangkan</span>
                </button>
              </template>
              <a :href="'/pos/dinein/meja/' + (selectedTrxDetail.meja ? (selectedTrxDetail.meja.id || selectedTrxDetail.meja) : selectedTrxDetail.meja_id) + '/checkout'"
                 class="inline-flex items-center gap-1.5 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs transition shadow-sm cursor-pointer active:scale-95">
                <span>Bayar</span>
                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
              </a>
              <button type="button" @click="showTrxDetailModal = false"
                      class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl text-xs transition cursor-pointer">
                Tutup
              </button>
            </div>
          </div>
        </template>

        {{-- Kalau Sudah Dibayar: [ Cetak Struk Pembayaran ] [ Tutup ] --}}
        <template x-if="selectedTrxDetail && selectedTrxDetail.status_raw === 'selesai'">
          <div class="flex items-center justify-between w-full gap-2">
            <button type="button"
                    @click="printReceiptPopup('/pos/dinein/pesanan/' + selectedTrxDetail.id + '/print-nota', 'PrintNota_' + selectedTrxDetail.id)"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-primary hover:bg-primary-container text-white font-extrabold rounded-xl text-xs transition shadow-sm cursor-pointer active:scale-95">
              <x-heroicon-o-document-text class="w-4 h-4" />
              <span>Cetak Struk Pembayaran</span>
            </button>
            <button type="button" @click="showTrxDetailModal = false"
                    class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl text-xs transition cursor-pointer">
              Tutup
            </button>
          </div>
        </template>

      </div>

    </div>
  </div>

  {{-- ── MODAL CHECKER MEJA (PENYAJIAN) ── --}}
  <div x-show="showCheckerModal" x-cloak x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-xl max-w-lg w-full shadow-2xl border border-gray-100 overflow-hidden" @click.outside="showCheckerModal = false">
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <div>
          <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
            <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-emerald-600" />
            Checker Meja
          </h3>
          <p class="text-xs text-slate-400 font-medium mt-0.5" x-text="checkerBill ? ('#' + (checkerBill.id_pesanan || ('DIN-' + checkerBill.id))) : ''"></p>
        </div>
        <button type="button" @click="showCheckerModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
      </div>

      <div class="px-6 py-4 space-y-3">
        <template x-if="checkerBill && checkerBill.meja">
          <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 bg-primary/5 text-primary font-black text-xs rounded-lg border border-primary/20">
              <x-heroicon-s-users class="w-3.5 h-3.5 inline-block mr-1" />
              <span x-text="checkerBill.meja.nomor_meja"></span>
            </span>
            <span class="text-xs font-semibold text-slate-500" x-text="checkerBill.nama_konsumen || 'Tamu'"></span>
          </div>
        </template>

        <template x-if="checkerBill && (!checkerBill.items || checkerBill.items.length === 0)">
          <div class="text-center py-8 text-xs text-slate-400 font-medium">Pesanan ini belum memiliki item / data belum dimuat. Muat ulang halaman untuk sinkronisasi.</div>
        </template>

        <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
          <template x-for="item in (checkerBill ? (checkerBill.items || []) : [])" :key="item.id">
            <div class="flex items-center justify-between gap-3 p-3 rounded-xl border"
                 :class="item.status_item === 'disajikan' ? 'bg-emerald-50/60 border-emerald-200' : 'bg-white border-slate-200'">
              <div class="min-w-0">
                <p class="text-sm font-extrabold text-slate-800 truncate">
                  <span class="px-1.5 py-0.5 bg-slate-100 text-slate-800 rounded-lg font-black text-xs mr-1" x-text="item.qty + 'x'"></span>
                  <span x-text="item.menu ? item.menu.nama : (item.nama_menu || 'Menu')"></span>
                </p>
                <template x-if="item.catatan">
                  <p class="text-xs text-amber-700 font-medium italic mt-0.5">* <span x-text="item.catatan"></span></p>
                </template>
                <span x-show="item.status_item === 'disajikan'" class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black uppercase border border-emerald-200">
                  <x-heroicon-o-check class="w-3 h-3" /> Disajikan
                </span>
              </div>
              <button type="button" @click="toggleStatusSajian(checkerBill.id, item.id)"
                      class="shrink-0 px-3.5 py-2 rounded-xl text-xs font-extrabold transition-colors"
                      :class="item.status_item === 'disajikan'
                          ? 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-50'
                          : 'bg-primary hover:bg-primary-container text-white'">
                <span x-text="item.status_item === 'disajikan' ? 'Batalkan' : 'Sajikan'"></span>
              </button>
            </div>
          </template>
        </div>
      </div>

      <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-2">
        <button type="button" @click="openCheckerPreview(checkerBill)" class="px-4 py-2.5 bg-neutral-900 text-white font-bold rounded-xl text-xs hover:bg-neutral-800 transition flex items-center gap-1.5 cursor-pointer shadow-xs">
          <x-heroicon-o-printer class="w-4 h-4 inline-block mr-1" /> Cetak Checker
        </button>
        <button type="button" @click="showCheckerModal = false" class="px-5 py-2.5 bg-primary text-white font-extrabold rounded-xl text-sm hover:bg-primary/90 transition-colors cursor-pointer">
          Tutup
        </button>
      </div>
    </div>
  </div>

  {{-- ── MODAL VOID / BATAL TRANSAKSI ── --}}
  <div x-show="showVoidModal" x-cloak x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-2xl border border-gray-100" @click.outside="showVoidModal = false">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-red-100 text-red-800 flex items-center justify-center font-bold text-sm">
            <x-heroicon-o-no-symbol class="w-5 h-5" />
          </div>
          <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Void Transaksi</h3>
        </div>
        <button type="button" @click="showVoidModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
      </div>

      <div class="space-y-3 text-xs">
        <p class="text-slate-600 font-medium">Anda akan membatalkan (Void) pesanan <strong x-text="trxToVoid ? ('#' + (trxToVoid.id_pesanan || ('DIN-' + trxToVoid.id))) : ''"></strong>. Transaksi tidak akan dihapus dan tetap tercatat untuk audit trail.</p>

        <div class="space-y-1.5">
          <label class="font-extrabold text-slate-700">Pilih Alasan Void <span class="text-red-500">*</span></label>
          <div class="relative">
            <select x-model="alasanVoidInput" class="w-full appearance-none h-11 pl-4 pr-10 text-sm font-semibold rounded-xl border border-gray-200 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none shadow-xs cursor-pointer">
              <option value="Salah Input Menu">Salah Input Menu</option>
              <option value="Request Pembatalan Pelanggan">Request Pembatalan Pelanggan</option>
              <option value="Lainnya">Lainnya</option>
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400">
              <x-heroicon-o-chevron-down class="w-4 h-4" />
            </span>
          </div>
        </div>

        <div class="space-y-1.5 mt-3">
          <label class="font-extrabold text-slate-700">Catatan Tambahan</label>
          <input type="text" x-model="catatanVoidInput" class="w-full h-11 px-3.5 text-sm font-extrabold rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary outline-none" placeholder="Misal: Pesanan ganda...">
        </div>

        <div class="mt-6 flex gap-3">
          <button type="button" @click="showVoidModal = false" class="flex-1 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold rounded-xl text-sm transition-colors">
            Tutup
          </button>
          <button type="button" @click="submitVoidOrder" :disabled="isSubmitting" class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-sm transition-colors shadow-sm disabled:opacity-50">
            <span x-show="!isSubmitting">Proses Void</span>
            <span x-show="isSubmitting">Memproses...</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ── MODAL PRINT STRUK (MULTI-SELECT) ── --}}
  <div x-show="showSavePrintModal || showCetakStrukModal"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95"
       x-transition:enter-end="opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100"
       x-transition:leave-end="opacity-0 scale-95"
       class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
       style="display: none;">
    <div class="bg-white rounded-xl p-6 max-w-sm w-full shadow-2xl relative">
      
      {{-- Modal Header --}}
      <div class="text-center mb-6 relative">
        <h3 class="text-lg font-bold text-body">Cetak Struk</h3>
        <p class="text-sm font-medium text-gray-500 mt-0.5">Pilih struk yang akan dicetak</p>
        <button type="button" @click="closePrintModal()" class="absolute right-0 top-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100/80 hover:bg-gray-200 text-gray-500 transition-colors">
          <x-heroicon-o-x-mark class="w-4 h-4" />
        </button>
      </div>

      {{-- List Options (Checkboxes) --}}
      <div class="space-y-3">
        
        {{-- Option 1: Struk Pelanggan --}}
        <label class="w-full group bg-white border rounded-xl p-4 flex items-center justify-between transition-all cursor-pointer select-none"
               :class="selectedPrintOptions.includes('konsumen') ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-200 hover:border-emerald-200'">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-colors shadow-sm border"
                     :class="selectedPrintOptions.includes('konsumen') ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-gray-50 text-gray-500 border-gray-100 group-hover:bg-white'">
                    <x-heroicon-o-document-text class="w-5 h-5" />
                </div>
                <span class="font-semibold text-sm transition-colors"
                      :class="selectedPrintOptions.includes('konsumen') ? 'text-emerald-800' : 'text-gray-700'">Struk Pelanggan</span>
            </div>
            <input type="checkbox" class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" 
                   value="konsumen" x-model="selectedPrintOptions">
        </label>

        {{-- Option 2: Struk Dapur --}}
        <label class="w-full group bg-white border rounded-xl p-4 flex items-center justify-between transition-all cursor-pointer select-none"
               :class="selectedPrintOptions.includes('dapur') ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-200 hover:border-emerald-200'">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-colors shadow-sm border"
                     :class="selectedPrintOptions.includes('dapur') ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-gray-50 text-gray-500 border-gray-100 group-hover:bg-white'">
                    <x-heroicon-o-fire class="w-5 h-5" />
                </div>
                <span class="font-semibold text-sm transition-colors"
                      :class="selectedPrintOptions.includes('dapur') ? 'text-emerald-800' : 'text-gray-700'">Struk Dapur</span>
            </div>
            <input type="checkbox" class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" 
                   value="dapur" x-model="selectedPrintOptions">
        </label>

        {{-- Option 3: Struk Checker Pesanan --}}
        <label class="w-full group bg-white border rounded-xl p-4 flex items-center justify-between transition-all cursor-pointer select-none"
               :class="selectedPrintOptions.includes('meja') ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-200 hover:border-emerald-200'">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-colors shadow-sm border"
                     :class="selectedPrintOptions.includes('meja') ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-gray-50 text-gray-500 border-gray-100 group-hover:bg-white'">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                </div>
                <span class="font-semibold text-sm transition-colors"
                      :class="selectedPrintOptions.includes('meja') ? 'text-emerald-800' : 'text-gray-700'">Struk Checker Pesanan (Meja)</span>
            </div>
            <input type="checkbox" class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" 
                   value="meja" x-model="selectedPrintOptions">
        </label>

      </div>

      {{-- Actions --}}
      <div class="mt-6 space-y-2">
        <button type="button" @click="executePrintSelection()" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
          <x-heroicon-o-printer class="w-5 h-5" />
          Cetak yang Dipilih
        </button>

        <button type="button" @click="closePrintModal()" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
          <span x-text="pendingCheckoutAction ? 'Lewati & Proses Pembayaran' : 'Lewati (Kembali)'"></span>
          <x-heroicon-o-arrow-right class="w-3 h-3" />
        </button>
      </div>
      
    </div>
  </div>

  {{-- ── MODAL RECEIPT PREVIEW ── --}}
  <div x-show="showReceiptPreview"
       x-transition.opacity
       class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
       style="display: none;">
    <div class="bg-gray-50 rounded-xl max-w-sm w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      
      {{-- Modal Header --}}
      <div class="bg-white px-5 py-4 flex items-center justify-between border-b border-gray-100 shrink-0">
        <h3 class="text-sm font-black text-primary tracking-wide" x-text="(receiptType === 'meja' ? 'Table Receipt' : 'Receipt Preview') + ' - Order #' + (previewTrx?.id_pesanan || ('DIN-' + previewTrx?.id))"></h3>
        <button type="button" @click="showReceiptPreview = false" class="text-gray-400 hover:text-gray-700 transition-colors">
          <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
      </div>

      {{-- Receipt Container (Scrollable) --}}
      <div class="p-6 overflow-y-auto flex-1 flex justify-center">
        <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-5 w-full font-mono text-xs leading-relaxed text-gray-800" style="width: 300px; min-height: 400px;">
          <div class="text-center font-bold text-sm mb-1 uppercase tracking-wider">{{ config('app.name', 'Resto Sunda') }}</div>
          <div class="text-center text-[10px] text-gray-500 mb-1">Jl Kebun Raya<br>6285797856221</div>
          
          <div class="border-b border-dashed border-gray-300 my-3"></div>
          
          <div class="grid grid-cols-[80px_1fr] gap-x-2">
            <span class="text-gray-500">Order #:</span>
            <span class="text-right font-bold" x-text="previewTrx?.id_pesanan || ('DIN-' + previewTrx?.id)"></span>
            
            <span class="text-gray-500">Date:</span>
            <span class="text-right" x-text="previewTrx ? (previewTrx.dibuat_pada || previewTrx.created_at) : ''"></span>
            
            <span class="text-gray-500">Table:</span>
            <span class="text-right" x-text="previewTrx?.meja?.nomor_meja || '-'"></span>
            
            <span class="text-gray-500">Customer:</span>
            <span class="text-right" x-text="previewTrx?.nama_konsumen || 'Guest'"></span>
            
            <span class="text-gray-500">Cashier:</span>
            <span class="text-right" x-text="(previewTrx?.pembayaran?.diverifikasi_oleh_pengguna?.name) || 'Staff'"></span>
          </div>

          <div class="border-b border-dashed border-gray-300 my-3"></div>
          
          <template x-for="item in (previewTrx?.items || [])" :key="item.id">
            <div class="flex justify-between mb-1">
              <span x-text="item.qty + 'x ' + (item.menu ? item.menu.nama : item.nama_menu)"></span>
              <span x-text="'Rp' + formatPrice((item.menu ? item.menu.harga : item.harga_satuan) * item.qty)"></span>
            </div>
          </template>

          <div class="border-b border-dashed border-gray-300 my-3"></div>
          
          <div class="flex justify-between text-gray-500 mb-0.5">
            <span>Subtotal</span>
            <span x-text="'Rp' + formatPrice((previewTrx?.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : i.harga_satuan) * i.qty), 0))"></span>
          </div>
          <div class="flex justify-between text-gray-500">
            <span>Tax (10%)</span>
            <span x-text="'Rp' + formatPrice(((previewTrx?.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : i.harga_satuan) * i.qty), 0)) * 0.1)"></span>
          </div>
          
          <div class="border-b border-dashed border-gray-300 my-3"></div>
          
          <div class="flex justify-between font-bold text-sm mb-1">
            <span>TOTAL</span>
            <span x-text="'Rp' + formatPrice(((previewTrx?.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : i.harga_satuan) * i.qty), 0)) * 1.1)"></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Payment:</span>
            <span class="uppercase" x-text="previewTrx?.pembayaran?.metode_bayar || 'OPEN-BILL'"></span>
          </div>
          
          <div class="mt-6 text-center text-[10px] text-gray-500 space-y-1">
            <p>Thank you for your order!</p>
            <p x-text="new Date().toLocaleString()"></p>
          </div>
        </div>
      </div>

      {{-- Modal Footer Actions --}}
      <div class="bg-white px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-3 shrink-0">
        <button type="button" @click="showReceiptPreview = false" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs flex items-center gap-2 hover:bg-gray-50 transition-colors">
          <x-heroicon-o-x-mark class="w-4 h-4" />
          Close
        </button>
        <div class="flex items-center gap-2">
          <a :href="'/pos/dinein/pesanan/' + (previewTrx?.id || '') + (receiptType === 'meja' ? '/print-meja?pdf=true' : '/print-nota?pdf=true')" target="_blank" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs flex items-center gap-2 hover:bg-gray-50 transition-colors">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
            Save PDF
          </a>
          <a :href="'/pos/dinein/pesanan/' + (previewTrx?.id || '') + (receiptType === 'meja' ? '/print-meja' : '/print-nota')" target="_blank" class="px-4 py-2.5 rounded-xl bg-primary-container hover:bg-primary-container text-white font-bold text-xs flex items-center gap-2 transition-colors shadow-sm">
            <x-heroicon-o-printer class="w-4 h-4" />
            <span x-text="receiptType === 'meja' ? 'Print Table' : 'Print Receipt'"></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- ── MODAL KITCHEN PREVIEW ── --}}
  <div x-show="showKitchenPreview"
       x-transition.opacity
       class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
       style="display: none;">
    <div class="bg-gray-50 rounded-xl max-w-sm w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      
      {{-- Modal Header --}}
      <div class="bg-white px-5 py-4 flex items-center justify-between border-b border-gray-100 shrink-0">
        <h3 class="text-sm font-black text-primary tracking-wide" x-text="'Kitchen Preview - Order #' + (previewTrx?.id_pesanan || ('ORD-' + previewTrx?.id))"></h3>
        <button type="button" @click="showKitchenPreview = false" class="text-gray-400 hover:text-gray-700 transition-colors">
          <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
      </div>

      {{-- Kitchen Container (Scrollable) --}}
      <div class="p-6 overflow-y-auto flex-1 flex justify-center">
        <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-5 w-full font-mono text-xs leading-relaxed text-gray-800" style="width: 300px; min-height: 400px;">
          <div class="text-center font-bold text-sm mb-1 uppercase tracking-wider">KITCHEN ORDER</div>
          
          <div class="border-b border-dashed border-gray-400 my-3"></div>
          
          <div class="font-bold mb-1" x-text="'Order #' + (previewTrx?.id_pesanan || ('ORD-' + previewTrx?.id))"></div>
          <div>Time: <span x-text="previewTrx ? (previewTrx.dibuat_pada || previewTrx.created_at) : ''"></span></div>
          <div>Type: DINE-IN</div>
          <div class="font-bold text-sm mt-1 uppercase">TABLE: <span x-text="previewTrx?.meja?.nomor_meja || '-'"></span></div>
          <div class="mt-1">Customer: <span x-text="previewTrx?.nama_konsumen || 'Guest'"></span></div>

          <div class="border-b border-dashed border-gray-400 my-3"></div>
          
          <template x-for="(item, index) in (previewTrx?.items || [])" :key="item.id">
            <div class="mb-2">
              <div class="font-bold" x-text="(index + 1) + '. ' + (item.menu ? item.menu.nama : item.nama_menu)"></div>
              <div class="ml-4 font-bold">QTY: <span x-text="item.qty"></span></div>
              <div x-show="item.catatan" class="ml-4 mt-1 italic" x-text="'Note: ' + item.catatan"></div>
            </div>
          </template>

          <div class="border-b border-dashed border-gray-400 my-3"></div>
          
          <div class="text-center font-bold mb-4 uppercase">
            EST. READY: <span x-text="new Date(new Date().getTime() + 15*60000).toLocaleString()"></span>
          </div>

          <div class="space-y-1">
            <div>[ ] PREPARED</div>
            <div>[ ] QUALITY CHECK</div>
            <div>[ ] READY TO SERVE</div>
          </div>
        </div>
      </div>

      {{-- Modal Footer Actions --}}
      <div class="bg-white px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-3 shrink-0">
        <button type="button" @click="showKitchenPreview = false" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs flex items-center gap-2 hover:bg-gray-50 transition-colors">
          <x-heroicon-o-x-mark class="w-4 h-4" />
          Close
        </button>
        <a :href="'/pos/dinein/pesanan/' + (previewTrx?.id || '') + '/print-dapur'" target="_blank" class="px-6 py-2.5 rounded-xl bg-primary-container hover:bg-primary-container text-white font-bold text-xs flex items-center gap-2 transition-colors shadow-sm ml-auto">
          <x-heroicon-o-printer class="w-4 h-4" />
          Print Kitchen
        </a>
      </div>
    </div>
  </div>

  {{-- ── MODAL PRATINJAU CETAK CHECKER (MEJA & DAPUR) ── --}}
  <div x-show="showCheckerPreviewModal"
       x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-3 sm:p-4"
       style="display: none;">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[90vh] sm:max-h-[85vh] animate-in fade-in zoom-in-95 duration-200"
         @click.outside="closeCheckerPreview()">

      {{-- Modal Header (No Print) --}}
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white shrink-0 no-print">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700 shadow-2xs">
            <x-heroicon-o-printer class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-sm font-extrabold text-slate-900 leading-tight">Pratinjau Cetak Checker</h3>
            <p class="text-[11px] font-medium text-slate-400 mt-0.5">Meja & Dapur &bull; Kertas Thermal 80mm</p>
          </div>
        </div>
        <button type="button" @click="closeCheckerPreview()"
                class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors flex items-center justify-center cursor-pointer"
                title="Tutup">
          <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
      </div>

      {{-- Receipt Preview Container (Scrollable, Printable) --}}
      <div class="p-4 sm:p-6 overflow-y-auto flex-1 min-h-0 bg-neutral-100 flex flex-col items-center custom-scrollbar">
        {{-- Thermal Paper Simulation (80mm width - Pure Monochrome Black & White) --}}
        <div id="printableCheckerArea"
             class="w-full max-w-[300px] bg-white rounded-none shadow-sm border border-neutral-300 p-4 text-black transition-all font-mono text-[11px] leading-tight select-text"
             style="font-family: 'Courier New', Courier, monospace; color: #000000; background-color: #ffffff;">

          {{-- ═══════════════ BAGIAN 1: MEJA CHECKER ═══════════════ --}}
          <div class="text-center font-bold text-xs uppercase tracking-wider text-black pb-0.5">
            MEJA CHECKER
          </div>

          <div class="border-b border-dashed border-black my-2"></div>

          <div class="space-y-1 text-[11px] text-black">
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Kode Pesanan</span>
              <span class="font-bold text-right"
                    x-text="checkerPreviewBill?.id_pesanan || ('DIN-' + (checkerPreviewBill?.id || ''))"></span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Meja</span>
              <span class="font-bold text-right"
                    x-text="checkerPreviewBill?.meja ? (typeof checkerPreviewBill.meja === 'object' ? ('Meja ' + (checkerPreviewBill.meja.nomor_meja?.replace(/^Meja\s*/i, '') || '-')) : ('Meja ' + String(checkerPreviewBill.meja).replace(/^Meja\s*/i, ''))) : '-'"></span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Konsumen</span>
              <span class="font-bold text-right truncate max-w-[160px]"
                    x-text="(checkerPreviewBill?.nama_konsumen || checkerPreviewBill?.pelanggan?.nama || 'Tamu').split(' – ')[0].split(' - ')[0].split(' | ')[0].replace(/\(\d+\s*tamu\)/i, '').trim()"></span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Kasir</span>
              <span class="text-right"
                    x-text="checkerPreviewBill?.kasir?.nama || '{{ auth()->user()->nama ?? 'Kasir BBC' }}'"></span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Waktu</span>
              <span class="text-right"
                    x-text="formatDateStr(checkerPreviewBill?.dibuat_pada || checkerPreviewBill?.created_at)"></span>
            </div>
          </div>

          <div class="border-b border-dashed border-black my-2"></div>

          <div class="font-bold text-black tracking-wider text-[11px] mb-1.5 uppercase">
            RINCIAN PESANAN
          </div>

          <div class="space-y-1">
            <template x-for="(item, idx) in (checkerPreviewBill ? (checkerPreviewBill.items || checkerPreviewBill.detail_pesanan || []) : [])" :key="'meja_' + (item.id || idx)">
              <div class="text-[11px] text-black leading-snug">
                <div class="flex items-start gap-1">
                  <span class="font-bold shrink-0" x-text="(item.qty || item.jumlah || 1) + 'x'"></span>
                  <span class="font-bold" x-text="(item.menu ? (item.menu.nama_menu || item.menu.nama) : (item.nama_menu || item.nama || 'Menu'))"></span>
                </div>
                <template x-if="item.catatan">
                  <div class="text-[10px] italic pl-4 text-black" x-text="'* ' + item.catatan"></div>
                </template>
              </div>
            </template>
          </div>

          <div class="border-b border-dashed border-black my-2.5"></div>

          {{-- ═══════════════ PEMISAH CHECKER MEJA & DAPUR ═══════════════ --}}
          <div class="my-3 text-center">
            <div class="border-b border-dashed border-black my-1"></div>
            <div class="text-[9px] font-bold tracking-widest text-black uppercase py-0.5">-- POTONG DI SINI --</div>
            <div class="border-b border-dashed border-black my-1"></div>
          </div>

          {{-- ═══════════════ BAGIAN 2: STRUK DAPUR CHECKER ═══════════════ --}}
          <div class="text-center font-bold text-xs uppercase tracking-wider text-black pb-0.5 pt-1">
            STRUK DAPUR CHECKER
          </div>

          <div class="border-b border-dashed border-black my-2"></div>

          <div class="space-y-1 text-[11px] text-black">
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Kode Pesanan</span>
              <span class="font-bold text-right"
                    x-text="checkerPreviewBill?.id_pesanan || ('DIN-' + (checkerPreviewBill?.id || ''))"></span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Meja</span>
              <span class="font-bold text-right"
                    x-text="checkerPreviewBill?.meja ? (typeof checkerPreviewBill.meja === 'object' ? ('Meja ' + (checkerPreviewBill.meja.nomor_meja?.replace(/^Meja\s*/i, '') || '-')) : ('Meja ' + String(checkerPreviewBill.meja).replace(/^Meja\s*/i, ''))) : '-'"></span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Konsumen</span>
              <span class="font-bold text-right truncate max-w-[160px]"
                    x-text="(checkerPreviewBill?.nama_konsumen || checkerPreviewBill?.pelanggan?.nama || 'Tamu').split(' – ')[0].split(' - ')[0].split(' | ')[0].replace(/\(\d+\s*tamu\)/i, '').trim()"></span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Kasir</span>
              <span class="text-right"
                    x-text="checkerPreviewBill?.kasir?.nama || '{{ auth()->user()->nama ?? 'Kasir BBC' }}'"></span>
            </div>
            <div class="flex justify-between items-start gap-2">
              <span class="shrink-0">Waktu</span>
              <span class="text-right"
                    x-text="formatDateStr(checkerPreviewBill?.dibuat_pada || checkerPreviewBill?.created_at)"></span>
            </div>
          </div>

          <div class="border-b border-dashed border-black my-2"></div>

          <div class="font-bold text-black tracking-wider text-[11px] mb-1.5 uppercase">
            RINCIAN PESANAN
          </div>

          <div class="space-y-1">
            <template x-for="(item, idx) in (checkerPreviewBill ? (checkerPreviewBill.items || checkerPreviewBill.detail_pesanan || []) : [])" :key="'dapur_' + (item.id || idx)">
              <div class="text-[11px] text-black leading-snug">
                <div class="flex items-start gap-1">
                  <span class="font-bold shrink-0" x-text="(item.qty || item.jumlah || 1) + 'x'"></span>
                  <span class="font-bold" x-text="(item.menu ? (item.menu.nama_menu || item.menu.nama) : (item.nama_menu || item.nama || 'Menu'))"></span>
                </div>
                <template x-if="item.catatan">
                  <div class="text-[10px] italic pl-4 text-black" x-text="'* ' + item.catatan"></div>
                </template>
              </div>
            </template>
          </div>

          <div class="border-b border-dashed border-black my-2"></div>

        </div>
      </div>

      {{-- Modal Footer Actions (No Print) --}}
      <div class="bg-white px-5 py-3.5 border-t border-slate-100 flex items-center justify-between gap-3 shrink-0 no-print">
        <button type="button"
                @click="closeCheckerPreview()"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition active:scale-[0.98] cursor-pointer">
          Kembali
        </button>
        <button type="button"
                @click="printCheckerDirect()"
                class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 transition active:scale-[0.98] shadow-sm cursor-pointer">
          <x-heroicon-o-printer class="w-4 h-4 text-white" />
          <span>Cetak Sekarang</span>
        </button>
      </div>

    </div>
  </div>

  {{-- ── MODAL PESANAN BERHASIL (SUCCESS MODAL) ── --}}
  <div x-show="showSuccessModal"
       x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95"
       x-transition:enter-end="opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100"
       x-transition:leave-end="opacity-0 scale-95"
       class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4"
       style="display: none;">
    <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 flex flex-col items-center text-center mx-auto"
         @click.outside="showSuccessModal = false">
        
      {{-- Close Button --}}
      <button type="button" @click="showSuccessModal = false"
              class="absolute top-3.5 right-3.5 w-8 h-8 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-center cursor-pointer">
        <x-heroicon-o-x-mark class="w-5 h-5" />
      </button>

      {{-- Success Icon Badge --}}
      <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center mb-3.5 shadow-2xs">
        <x-heroicon-o-check-circle class="w-8 h-8 stroke-[2.2]" />
      </div>

      <h3 class="text-lg font-black text-gray-900 leading-tight">Pesanan Berhasil!</h3>
      <p class="text-xs text-gray-500 font-medium mt-0.5">Pesanan baru telah berhasil disimpan ke daftar.</p>
      
      {{-- Order Summary Box --}}
      <div class="w-full mt-4 bg-gray-50/80 rounded-xl p-3.5 border border-gray-100 space-y-2 text-xs text-left">
        <div class="flex items-center justify-between pb-1.5 border-b border-gray-100">
          <span class="text-gray-500 font-medium">Kode Pesanan</span>
          <span class="font-bold text-gray-900 font-mono" x-text="savedPesananObject ? (savedPesananObject.id_pesanan || ('DIN-' + savedPesananObject.id)) : '-'"></span>
        </div>
        <div class="flex items-center justify-between" x-show="savedPesananObject?.meja">
          <span class="text-gray-500 font-medium">Meja</span>
          <span class="font-bold text-gray-900" x-text="savedPesananObject?.meja ? (typeof savedPesananObject.meja === 'object' ? (savedPesananObject.meja.nomor_meja?.startsWith('Meja') ? savedPesananObject.meja.nomor_meja : 'Meja ' + savedPesananObject.meja.nomor_meja) : (String(savedPesananObject.meja).startsWith('Meja') ? savedPesananObject.meja : 'Meja ' + savedPesananObject.meja)) : '-'"></span>
        </div>
        <div class="flex items-center justify-between" x-show="savedPesananObject?.nama_konsumen">
          <span class="text-gray-500 font-medium">Pelanggan</span>
          <span class="font-bold text-gray-900" x-text="(savedPesananObject?.nama_konsumen || 'Tamu').split(' – ')[0].split(' - ')[0].split(' | ')[0].replace(/\(\d+\s*tamu\)/i, '').trim()"></span>
        </div>
        <div class="flex items-center justify-between pt-1.5 border-t border-gray-100">
          <span class="font-extrabold text-gray-700">Total Tagihan</span>
          <span class="font-black text-sm text-primary" x-text="savedPesananObject ? ('Rp ' + formatPrice(savedPesananObject.total_tagihan)) : 'Rp 0'"></span>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div class="w-full mt-4 space-y-2">
        <button type="button"
                @click="showSuccessModal = false; openCheckerPreview(savedPesananObject || { id: savedPesananId })"
                class="w-full h-10 bg-primary hover:bg-primary-container text-white font-extrabold text-xs rounded-xl shadow-xs flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer">
          <x-heroicon-o-printer class="w-4 h-4 text-emerald-400 pointer-events-none" />
          <span>Cetak Meja & Dapur Checker</span>
        </button>
        <button type="button"
                @click="showSuccessModal = false; resetCartPanel()"
                class="w-full h-10 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 font-bold text-xs rounded-xl transition-all shadow-2xs flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer">
          <x-heroicon-o-plus-circle class="w-4 h-4 text-gray-400" />
          <span>Buat Pesanan Baru</span>
        </button>
      </div>
    </div>
  </div>

</div>

@endsection
