@extends('layouts.pos')

@section('content')
{{-- ╔══════════════════════════════════════╗ --}}
{{-- ║  BBC RESTO — POINT OF SALE (POS)     ║ --}}
{{-- ╚══════════════════════════════════════╝ --}}
<style>
  .pos-root        { font-family: 'Plus Jakarta Sans', sans-serif; }
  .chip-active     { background:#0F2E23; color:#ffffff; border: 1px solid #0F2E23; }
  .chip-default    { background:#FFFFFF; color:#374151; border: 1px solid #E5E7EB; }
  .chip-default:hover { background:#F9FAFB; color:#111827; border-color:#D1D5DB; }
  .card-menu:hover { border-color:rgba(15,46,35,.35); box-shadow:0 8px 24px rgba(15,46,35,.08); transform: translateY(-2px); }
  .mono            { font-family:'Anonymous Pro', monospace; letter-spacing:.05em; }
</style>
<script>
function posSystemData() {
  return {
    // View state
    leftView: '{{ in_array(request("view"), ["open_bills", "pesanan_belum_dibayar"]) ? "open_bills" : (request("view", "menu")) }}',
    activeCategory: 'semua',
    searchQuery: '',
    tableSearch: '',
    tableFilter: 'semua',
    openBillSearch: '',
    openBillFilter: 'semua',
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

    // Modals Detail & Void & Cetak Struk
    selectedTrxDetail: null,
    showTrxDetailModal: false,
    showVoidModal: false,
    trxToVoid: null,
    alasanVoidInput: 'Salah Input Menu',
    catatanVoidInput: '',
    showCetakStrukModal: false,
    showSavePrintModal: false,
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
        return Swal.fire({ icon: 'warning', title: 'Pilih Struk', text: 'Pilih minimal 1 jenis struk yang akan dicetak!', confirmButtonColor: '#0F2E23' });
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

    get filteredRiwayat() {
      let list = this.riwayatTransaksi || [];

      // 1. Filter Tanggal
      const todayStr = (new Date()).toISOString().slice(0,10);
      if (this.riwayatDateQuick === 'today') {
        list = list.filter(t => t.created_at && t.created_at.startsWith(todayStr));
      } else if (this.riwayatDateQuick === 'yesterday') {
        const y = new Date(); y.setDate(y.getDate() - 1);
        const yStr = y.toISOString().slice(0,10);
        list = list.filter(t => t.created_at && t.created_at.startsWith(yStr));
      } else if (this.riwayatDateQuick === 'last7') {
        const d7 = new Date(); d7.setDate(d7.getDate() - 7);
        list = list.filter(t => t.created_at && (new Date(t.created_at)) >= d7);
      } else if (this.riwayatDateQuick === 'this_month') {
        const mStr = (new Date()).toISOString().slice(0,7);
        list = list.filter(t => t.created_at && t.created_at.startsWith(mStr));
      } else if (this.riwayatDateQuick === 'custom' && (this.riwayatStartDate || this.riwayatEndDate)) {
        list = list.filter(t => {
          if (!t.created_at) return false;
          const d = t.created_at.slice(0,10);
          if (this.riwayatStartDate && d < this.riwayatStartDate) return false;
          if (this.riwayatEndDate && d > this.riwayatEndDate) return false;
          return true;
        });
      }

      // 2. Filter Kasir
      if (this.riwayatKasirFilter !== 'semua') {
        list = list.filter(t => t.dibuka_oleh == this.riwayatKasirFilter || (t.pembayaran && t.pembayaran.diproses_oleh == this.riwayatKasirFilter));
      }

      // 3. Filter Status
      if (this.riwayatStatusFilter !== 'semua') {
        list = list.filter(t => t.status === this.riwayatStatusFilter);
      }

      // 4. Filter Metode Bayar
      if (this.riwayatPayFilter === 'cash') {
        list = list.filter(t => t.pembayaran && t.pembayaran.metode_bayar === 'cash');
      } else if (this.riwayatPayFilter === 'qris') {
        list = list.filter(t => t.pembayaran && ['qris', 'nontunai', 'kartu'].includes(t.pembayaran.metode_bayar));
      }

      // 5. Smart Search
      const query = (this.searchQuery || this.riwayatSearch || '').trim().toLowerCase();
      if (query) {
        list = list.filter(t => 
          (t.nama_konsumen && t.nama_konsumen.toLowerCase().includes(query)) ||
          (t.kode_pesanan && t.kode_pesanan.toLowerCase().includes(query)) ||
          (t.id && ('DIN-' + t.id).toLowerCase().includes(query)) ||
          (t.meja && t.meja.nomor_meja && t.meja.nomor_meja.toString().toLowerCase().includes(query))
        );
      }

      return list;
    },

    get summaryOmzet() {
      return this.filteredRiwayat.filter(t => t.status === 'lunas').reduce((s, t) => s + (t.items || []).reduce((is, i) => is + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0), 0);
    },

    openDetailModal(trx) {
      this.selectedTrxDetail = trx;
      this.showTrxDetailModal = true;
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
          Swal.fire({ icon: 'success', title: 'Transaksi Dibatalkan (Void)!', text: data.message, confirmButtonColor: '#0F2E23' });
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal void transaksi', confirmButtonColor: '#0F2E23' });
        }
      } catch(e) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Terjadi kesalahan jaringan.', confirmButtonColor: '#0F2E23' });
      } finally {
        this.isSubmitting = false;
      }
    },

    exportToCsv() {
      const rows = [
        ['No. Order', 'Pelanggan', 'Meja', 'Waktu', 'Kasir', 'Metode Bayar', 'Status', 'Total Tagihan (Rp)']
      ];
      this.filteredRiwayat.forEach(t => {
        const total = (t.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0);
        const kasirName = (t.pembayaran && t.pembayaran.diproses_oleh && t.pembayaran.diproses_oleh.name) ? t.pembayaran.diproses_oleh.name : ((t.kasir && t.kasir.name) ? t.kasir.name : 'Kasir');
        rows.push([
          t.kode_pesanan || ('DIN-' + t.id),
          `"${t.nama_konsumen || ''}"`,
          t.meja ? t.meja.nomor_meja : '-',
          t.created_at || '',
          `"${kasirName}"`,
          t.pembayaran ? t.pembayaran.metode_bayar : 'LUNAS',
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
    showSavePrintModal: false,
    savedPesananId: null,
    savedPesananObject: null,
    activePrintEmbed: 'gabungan',

    openBills: @json($openBills),

    // ── Computed ────────────────────────────────────
    get totalPrice() { return this.cart.reduce((t, i) => t + i.harga * i.qty, 0); },
    get totalQty()   { return this.cart.reduce((t, i) => t + i.qty, 0); },

    formatPrice(n) {
      if (!n) return '0';
      return Number(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    printSilentIframe(url) {
      if (!url) return;
      let iframe = document.getElementById('posPrintIframe');
      if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'posPrintIframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        iframe.style.visibility = 'hidden';
        document.body.appendChild(iframe);
      }
      iframe.src = url;
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
          confirmButtonColor: '#0F2E23',
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
        bill.sub_status = subStatus;
      }
      fetch('/pos/dinein/pesanan/' + pesananId + '/sub-status', {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ sub_status: subStatus })
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
          Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal mengubah status ketersediaan menu', confirmButtonColor: '#0F2E23' });
        }
      } catch(e) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Terjadi kesalahan koneksi internet / server.', confirmButtonColor: '#0F2E23' });
      }
    },

    // ── Submit Order ────────────────────────────────
    async submitOrder(action) {
      if (!this.selectedTable) return Swal.fire({ icon: 'warning', title: 'Pilih Meja', text: 'Mohon pilih nomor meja terlebih dahulu!', confirmButtonColor: '#0F2E23' });
      if (!this.customerName.trim()) return Swal.fire({ icon: 'warning', title: 'Nama Konsumen Kosong', text: 'Mohon isi nama konsumen terlebih dahulu!', confirmButtonColor: '#0F2E23' });
      if (!this.cart.length) return Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Keranjang belanjaan masih kosong!', confirmButtonColor: '#0F2E23' });

      this.isSubmitting = true;
      try {
        const res = await fetch('{{ route('pos.dinein.store-pos') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify({
            meja_id: this.selectedTable,
            nama_konsumen: this.customerPhone ? `${this.customerName} – ${this.customerPhone}` : this.customerName,
            items: this.cart
          })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            // Dynamic Alpine state update without page reload
            if (data.pesanan) {
              if (Array.isArray(this.openBills)) {
                const existingIdx = this.openBills.findIndex(b => b.id === data.pesanan.id);
                if (existingIdx !== -1) {
                  this.openBills[existingIdx] = data.pesanan;
                } else {
                  this.openBills.unshift(data.pesanan);
                }
              } else {
                this.openBills = Object.values(this.openBills);
                this.openBills.unshift(data.pesanan);
              }
            }

            if (this.emptyTablesCount > 0) {
              this.emptyTablesCount = Math.max(0, this.emptyTablesCount - 1);
            }

            this.savedPesananId = data.pesanan_id;
            this.savedPesananObject = data.pesanan;
            this.rightPanelMode = 'cart';

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

            // Show Cetak Struk Modal (Image 2) and pass action + mejaId
            this.openCetakStrukModal(data.pesanan_id, action, this.selectedTable);

            // Reset cart & form inputs smoothly
            this.cart = [];
            this.customerName = '';
            this.customerPhone = '';
            this.selectedTable = null;
            this.selectedTableLabel = '';

        } else { Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: data.message || 'Gagal menyimpan pesanan', confirmButtonColor: '#0F2E23' }); }
      } catch(e) { Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Terjadi kesalahan jaringan.', confirmButtonColor: '#0F2E23' }); }
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
                // Update local state
                const bill = this.openBills.find(b => b.id === billId);
                if (bill && bill.items) {
                    const item = bill.items.find(i => i.id === itemId);
                    if (item) {
                        item.status_sajian = data.status_sajian;
                    }
                }
            }
        } catch(e) {
            console.error('Toggle sajian error:', e);
        }
    },

    proceedToCheckout(bill) {
        if (!bill.items || bill.items.length === 0) return;
        window.location.href = '/pos/dinein/meja/' + bill.id + '/checkout';
    }
  };
}

window.posSystem = posSystemData;
document.addEventListener('alpine:init', () => {
  if (typeof Alpine !== 'undefined') {
    Alpine.data('posSystem', posSystemData);
  }
});
</script>

<div x-data="posSystem()" class="pos-root h-screen w-full flex overflow-hidden bg-[#f5f5f0] text-[#111827]">

  {{-- ─────────────────────────────── LEFT PANEL ────────────────────────────── --}}
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    {{-- ── TOPBAR ──────────────────────────────────────────────────── --}}
    <header class="bg-white/90 backdrop-blur-md border-b border-gray-200/80 px-6 py-3.5 shrink-0 z-10 space-y-3">
      
      {{-- BARIS 1: Header Top Bar (Judul, 2 Tab Utama, & Utility Icon Buttons) --}}
      <div class="flex items-center justify-between gap-4">

        {{-- Brand Title & 2 TAB UTAMA (Katalog Menu & Open Bills) --}}
        <div class="flex items-center gap-5">
          {{-- Brand Mark --}}
          <div class="shrink-0 flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#0F2E23] flex items-center justify-center shadow-xs">
              <x-heroicon-o-calculator class="w-5 h-5 text-emerald-300" style="width: 20px; height: 20px;" />
            </div>
            <div>
              <p class="text-[17px] font-extrabold text-[#0F2E23] leading-none tracking-tight">Point of Sale</p>
            </div>
          </div>

          {{-- 2 TAB UTAMA KHUSUS (Katalog Menu & List Pesanan Dine In) --}}
          <div class="flex items-center gap-1 bg-gray-100/90 p-1 rounded-2xl border border-gray-200/70">
            {{-- Tab 1: Katalog Menu --}}
            <button type="button" @click="leftView = 'menu'"
                    :class="leftView === 'menu' ? 'bg-[#0F2E23] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60'"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-xs font-extrabold transition-all active:scale-95">
              <x-heroicon-o-sparkles class="w-4 h-4 shrink-0" style="width: 16px; height: 16px;" />
              <span>Katalog Menu</span>
            </button>

            {{-- Tab 2: List Pesanan Dine In --}}
            <button type="button" @click="leftView = 'open_bills'"
                    :class="leftView === 'open_bills' ? 'bg-[#0F2E23] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60'"
                    class="inline-flex items-center gap-2 px-4 h-9 rounded-xl text-xs font-extrabold transition-all active:scale-95">
              <x-heroicon-o-receipt-percent class="w-4 h-4 shrink-0" style="width: 16px; height: 16px;" />
              <span>List Pesanan Dine In</span>
              {{-- Badge Angka Kecil (Pill) --}}
              <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black transition-colors"
                    :class="leftView === 'open_bills' ? 'bg-emerald-400 text-[#0F2E23]' : 'bg-[#0F2E23] text-white'"
                    x-text="openBills.length"></span>
            </button>
          </div>
        </div>

        {{-- Search Input & Utility Icon Buttons (Pojok Kanan Atas) --}}
        <div class="flex items-center gap-2 shrink-0">

          {{-- Search Bar (Hanya tampil jika bukan tampilan QR) --}}
          <div class="relative w-44 md:w-56" x-show="leftView !== 'qr'">
            <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-gray-400 pointer-events-none" style="width: 16px; height: 16px;" />
            <template x-if="leftView === 'menu'">
              <input x-model="searchQuery" type="text" placeholder="Cari menu…"
                     class="w-full h-9 pl-9 pr-7 text-xs font-medium rounded-xl border border-gray-200 bg-gray-50/60 focus:bg-white focus:border-[#0F2E23] focus:ring-1 focus:ring-[#0F2E23]/10 outline-none transition">
            </template>
            <template x-if="leftView === 'meja'">
              <input x-model="tableSearch" type="text" placeholder="Cari meja…"
                     class="w-full h-9 pl-9 pr-7 text-xs font-medium rounded-xl border border-gray-200 bg-gray-50/60 focus:bg-white focus:border-[#0F2E23] focus:ring-1 focus:ring-[#0F2E23]/10 outline-none transition">
            </template>
            <template x-if="leftView === 'open_bills'">
              <input x-model="openBillSearch" type="text" placeholder="Cari pesanan belum dibayar (no. order / pelanggan / meja)…"
                     class="w-full h-9 pl-9 pr-7 text-xs font-medium rounded-xl border border-gray-200 bg-gray-50/60 focus:bg-white focus:border-[#0F2E23] focus:ring-1 focus:ring-[#0F2E23]/10 outline-none transition">
            </template>
            <template x-if="leftView === 'riwayat'">
              <input x-model="riwayatSearch" type="text" placeholder="Cari riwayat (no. order / pelanggan / meja)…"
                     class="w-full h-9 pl-9 pr-7 text-xs font-medium rounded-xl border border-gray-200 bg-gray-50/60 focus:bg-white focus:border-[#0F2E23] focus:ring-1 focus:ring-[#0F2E23]/10 outline-none transition">
            </template>
            <button x-show="(leftView === 'menu' && searchQuery) || (leftView === 'meja' && tableSearch) || (leftView === 'open_bills' && openBillSearch) || (leftView === 'riwayat' && riwayatSearch)"
                    @click="searchQuery = ''; tableSearch = ''; openBillSearch = ''; riwayatSearch = ''"
                    class="absolute right-2.5 top-1.5 text-gray-400 hover:text-gray-600 text-sm leading-none">&times;</button>
          </div>

          {{-- 1. Utility Icon Button: Riwayat Transaksi Lunas --}}
          <button type="button" @click="leftView = (leftView === 'riwayat') ? 'menu' : 'riwayat'"
                  title="Riwayat Transaksi Lunas"
                  :class="leftView === 'riwayat' ? 'bg-[#0F2E23] text-white border-[#0F2E23]' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                  class="w-9 h-9 rounded-xl border flex items-center justify-center transition-all shadow-2xs">
            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
          </button>

          {{-- 2. Utility Icon Button: Manajemen Meja --}}
          <button type="button" @click="leftView = (leftView === 'meja') ? 'menu' : 'meja'"
                  title="Manajemen Meja"
                  :class="leftView === 'meja' ? 'bg-[#0F2E23] text-white border-[#0F2E23]' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                  class="w-9 h-9 rounded-xl border flex items-center justify-center transition-all shadow-2xs">
            <x-heroicon-o-squares-2x2 class="w-4 h-4 shrink-0" style="width: 18px; height: 18px;" />
          </button>

        </div>
      </div>

      {{-- BARIS 2: Filter Kategori & Status (Pill/Chip Horizontal Scroll) ───────────── --}}
      <div x-show="leftView === 'menu'" class="flex overflow-x-auto no-scrollbar gap-2 pt-1 pb-0.5">
        <button @click="activeCategory = 'semua'"
                :class="activeCategory === 'semua' ? 'chip-active shadow-xs' : 'chip-default'"
                class="shrink-0 inline-flex items-center gap-1.5 px-4 h-9 rounded-xl text-xs font-extrabold transition-all hover:scale-[1.02]">
          <x-heroicon-o-sparkles class="w-4 h-4 shrink-0" style="width: 15px; height: 15px;" />
          <span>Semua Menu</span>
        </button>
        @foreach($kategoris as $kategori)
        <button @click="activeCategory = '{{ $kategori->id }}'"
                :class="activeCategory === '{{ $kategori->id }}' ? 'chip-active shadow-xs' : 'chip-default'"
                class="shrink-0 inline-flex items-center px-4 h-9 rounded-xl text-xs font-extrabold whitespace-nowrap transition-all hover:scale-[1.02]">
          {{ $kategori->nama }}
        </button>
        @endforeach
      </div>

      <div x-show="leftView === 'meja'" class="flex items-center gap-2 pt-1 pb-0.5 overflow-x-auto no-scrollbar">
        <span class="text-xs text-gray-500 font-bold mr-1 shrink-0">Filter Meja:</span>
        <button @click="tableFilter = 'semua'" :class="tableFilter === 'semua' ? 'chip-active shadow-xs' : 'chip-default'"
                class="inline-flex items-center px-3.5 h-9 rounded-xl text-xs font-extrabold transition-all shrink-0">Semua</button>
        <button @click="tableFilter = 'kosong'" :class="tableFilter === 'kosong' ? 'bg-[#0F2E23] text-white font-extrabold' : 'bg-white text-emerald-900 border border-emerald-200 font-bold'"
                class="inline-flex items-center gap-1.5 px-3.5 h-9 rounded-xl text-xs transition-all shrink-0">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>Kosong
        </button>
        <button @click="tableFilter = 'terisi'" :class="tableFilter === 'terisi' ? 'bg-amber-800 text-white font-extrabold' : 'bg-white text-amber-950 border border-amber-300 font-bold'"
                class="inline-flex items-center gap-1.5 px-3.5 h-9 rounded-xl text-xs transition-all shrink-0">
          <span class="w-2 h-2 rounded-full bg-amber-600"></span>Terisi
        </button>
      </div>

      <div x-show="leftView === 'open_bills'" class="flex items-center gap-2 pt-1 pb-0.5 overflow-x-auto no-scrollbar">
        <span class="text-xs text-gray-500 font-bold mr-1 shrink-0">Filter Tagihan:</span>
        <button @click="openBillFilter = 'semua'" :class="openBillFilter === 'semua' ? 'chip-active shadow-xs' : 'chip-default'"
                class="inline-flex items-center px-3.5 h-9 rounded-xl text-xs font-extrabold transition-all shrink-0">Semua Tagihan</button>
        <button @click="openBillFilter = 'menunggu_bayar'" :class="openBillFilter === 'menunggu_bayar' ? 'bg-amber-800 text-white font-extrabold' : 'bg-white text-amber-950 border border-amber-300 font-bold'"
                class="inline-flex items-center gap-1.5 px-3.5 h-9 rounded-xl text-xs transition-all shrink-0">
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>Menunggu Bayar
        </button>
      </div>

      {{-- BARIS 2: Filter Riwayat Transaksi (Minimalist & Clean) --}}
      <div x-show="leftView === 'riwayat'" class="space-y-2 pt-1 pb-0.5">
        <div class="flex flex-wrap items-center justify-between gap-3">

          {{-- Quick Date Buttons --}}
          <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <button type="button" @click="riwayatDateQuick = 'today'"
                    :class="riwayatDateQuick === 'today' ? 'chip-active shadow-2xs' : 'chip-default'"
                    class="px-3.5 h-8 rounded-xl text-xs font-extrabold transition-all shrink-0">Hari Ini</button>
            <button type="button" @click="riwayatDateQuick = 'yesterday'"
                    :class="riwayatDateQuick === 'yesterday' ? 'chip-active shadow-2xs' : 'chip-default'"
                    class="px-3.5 h-8 rounded-xl text-xs font-extrabold transition-all shrink-0">Kemarin</button>
            <button type="button" @click="riwayatDateQuick = 'last7'"
                    :class="riwayatDateQuick === 'last7' ? 'chip-active shadow-2xs' : 'chip-default'"
                    class="px-3.5 h-8 rounded-xl text-xs font-extrabold transition-all shrink-0">7 Hari Terakhir</button>
            <button type="button" @click="riwayatDateQuick = 'this_month'"
                    :class="riwayatDateQuick === 'this_month' ? 'chip-active shadow-2xs' : 'chip-default'"
                    class="px-3.5 h-8 rounded-xl text-xs font-extrabold transition-all shrink-0">Bulan Ini</button>
            <button type="button" @click="riwayatDateQuick = 'semua'"
                    :class="riwayatDateQuick === 'semua' ? 'chip-active shadow-2xs' : 'chip-default'"
                    class="px-3.5 h-8 rounded-xl text-xs font-extrabold transition-all shrink-0">Semua Waktu</button>
          </div>

          {{-- Clean Minimal Selects --}}
          <div class="flex items-center gap-2">


            <select x-model="riwayatStatusFilter" class="h-8 px-3 text-xs font-bold rounded-xl border border-gray-200 bg-white text-gray-700 outline-none focus:border-[#0F2E23]">
              <option value="semua">Semua Status</option>
              <option value="lunas">Lunas</option>
              <option value="menunggu_pembayaran">Pending</option>
              <option value="void">Void / Batal</option>
            </select>

            <select x-model="riwayatPayFilter" class="h-8 px-3 text-xs font-bold rounded-xl border border-gray-200 bg-white text-gray-700 outline-none focus:border-[#0F2E23]">
              <option value="semua">Semua Bayar</option>
              <option value="cash">Tunai (Cash)</option>
              <option value="qris">Nontunai (QRIS)</option>
            </select>

            <button type="button" @click="exportToCsv()"
                    title="Export CSV"
                    class="h-8 px-3.5 rounded-xl bg-[#0F2E23] hover:bg-[#0a1f17] text-white font-extrabold text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
              <i class="fa-solid fa-download text-[10px]"></i>
              <span>Export CSV</span>
            </button>
          </div>

        </div>
      </div>

      {{-- BARIS 2: Filter QR Scan Menu --}}
      <div x-show="leftView === 'qr'" class="flex items-center justify-between pt-1 pb-0.5">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
          <button type="button" class="chip-active shadow-2xs px-3.5 h-8 rounded-xl text-xs font-extrabold transition-all shrink-0">Semua Meja ({{ $mejas->count() }})</button>
        </div>
        <a href="{{ route('pos.dinein.print-qr') }}" target="_blank"
           class="h-8 px-3.5 rounded-xl bg-[#0F2E23] hover:bg-[#0a1f17] text-white font-extrabold text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
          <i class="fa-solid fa-print text-xs text-emerald-400"></i>
          <span>Cetak Semua QR</span>
        </a>
      </div>
    </header>

    {{-- ══════════════════════  VIEW 1 · MENU CATALOG  ══════════════════════ --}}
    <div x-show="leftView === 'menu'" class="flex-1 overflow-y-auto p-4 bg-white">
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($menus as $menu)
        @php $isHabis = $menu->isHabis(); @endphp
        <div x-show="(activeCategory === 'semua' || activeCategory == '{{ $menu->kategori_menu_id }}') && ('{{ strtolower(addslashes($menu->nama)) }}'.includes(searchQuery.toLowerCase()))"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @if(!$isHabis) @click="addToCart({{ $menu->id }}, '{{ addslashes($menu->nama) }}', {{ $menu->harga }})" @endif
             class="group cursor-pointer flex flex-col bg-white border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 hover:border-gray-300 transition-all duration-300 rounded-2xl overflow-hidden {{ $isHabis ? 'opacity-50 grayscale pointer-events-none select-none' : '' }}">

          {{-- Thumbnail --}}
          <div class="relative w-full aspect-[4/3] bg-gray-50 border-b border-gray-100">
            @if($menu->foto)
              <img src="{{ Storage::url($menu->foto) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 {{ $isHabis ? 'grayscale opacity-60' : '' }}" alt="{{ $menu->nama }}">
            @else
              @php
                  $words = explode(' ', $menu->nama);
                  $initials = '';
                  foreach (array_slice($words, 0, 3) as $w) {
                      $initials .= strtoupper(substr($w, 0, 1));
                  }
              @endphp
              <div class="w-full h-full flex items-center justify-center bg-gray-50 {{ $isHabis ? 'grayscale opacity-60' : '' }}">
                  <span class="text-3xl font-black text-gray-300 tracking-widest">{{ $initials }}</span>
              </div>
            @endif

            {{-- Category Label --}}
            <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-gray-700 text-[10px] font-bold px-2.5 py-1 rounded-full border border-gray-100">
              {{ Str::limit($menu->kategori->nama ?? 'Menu', 18) }}
            </span>

            {{-- Overlay Habis Badge --}}
            @if($isHabis)
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-[1px] flex items-center justify-center">
              <span class="bg-red-600 text-white text-[11px] font-black px-3 py-1 rounded-xl shadow-md tracking-wider uppercase">
                HABIS
              </span>
            </div>
            @endif
          </div>

          {{-- Info --}}
          <div class="p-3.5 flex-1 flex flex-col justify-between gap-2">
            <div>
              <p class="text-[14px] font-extrabold text-[#111827] leading-snug line-clamp-2">{{ $menu->nama }}</p>
            </div>
            <div class="flex items-center justify-between mt-auto pt-1">
              <span class="text-[15px] font-black text-[#0F2E23]">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
              @if(!$isHabis)
              <button type="button" class="w-7 h-7 rounded-xl bg-[#0F2E23]/10 text-[#0F2E23] flex items-center justify-center hover:bg-[#0F2E23] hover:text-white transition-colors">
                <i class="fa-solid fa-plus text-xs"></i>
              </button>
              @endif
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- ══════════════════════  VIEW 2 · MANAJEMEN MEJA  ══════════════════════ --}}
    <div x-show="leftView === 'meja'" class="flex-1 overflow-y-auto p-4 md:p-6 pb-8 bg-[#f5f5f0]">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-base font-extrabold text-[#0F2E23]">Manajemen Meja Resto</h2>
          <p class="text-xs text-gray-500 font-medium">Pilih meja untuk membuat pesanan baru atau melihat tagihan aktif</p>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($mejas as $meja)
        @php
          $openBillMeja = $openBills->firstWhere('meja_id', $meja->id);
          $subStatusMeja = $openBillMeja->sub_status ?? 'kosong';
        @endphp
        <div x-show="tableFilter === 'semua' || (tableFilter === 'kosong' && '{{ $meja->status }}' === 'kosong') || (tableFilter === 'terisi' && '{{ $meja->status }}' !== 'kosong') || (tableFilter === '{{ $subStatusMeja }}')"
             class="bg-white border border-gray-200/80 rounded-2xl p-4 flex flex-col justify-between space-y-3 transition-all duration-150 shadow-xs hover:border-[#0F2E23]/40 {{ $meja->status !== 'kosong' ? 'bg-amber-50/40 border-amber-200' : '' }}">
          
          <div class="flex items-center justify-between">
            <span class="text-base font-black text-slate-900">{{ $meja->nomor_meja }}</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase border {{ $meja->status === 'kosong' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-900 border-amber-300' }}">
              {{ $meja->status === 'kosong' ? 'Kosong' : 'Terisi' }}
            </span>
          </div>

          {{-- Buttons --}}
          <div class="flex flex-col gap-2 mt-auto pt-2">
            <button type="button" @click="selectTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                    class="w-full py-2 rounded-xl text-xs font-extrabold transition-all bg-[#0F2E23] hover:bg-[#0a1f17] text-white active:scale-95 shadow-2xs">
              <span x-text="selectedTable == {{ $meja->id }} ? '✓ Terpilih' : 'Pilih Meja'"></span>
            </button>
            @if($meja->status !== 'kosong')
            <form action="{{ route('pos.dinein.clear-table', $meja->id) }}" method="POST" id="form-clear-{{ $meja->id }}">
              @csrf
              @method('PATCH')
              <button type="button" 
                      @click="confirmClearTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                      class="w-full py-1.5 rounded-xl text-[11px] font-extrabold text-amber-900 bg-amber-100/90 border border-amber-300 hover:bg-amber-200 transition-colors flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-broom text-[10px] text-amber-700"></i>
                <span>Kosongkan Meja</span>
              </button>
            </form>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- ══════════════════════  VIEW 3 · PESANAN BELUM DIBAYAR  ════════════════════════ --}}
    <div x-show="leftView === 'open_bills'" class="flex-1 overflow-y-auto p-4 md:p-6 pb-8 bg-[#f5f5f0]">

      {{-- Header strip --}}
      <div class="flex items-center justify-between mb-4 bg-white border border-gray-200/80 rounded-2xl px-5 py-3.5 shadow-xs">
        <div>
          <p class="text-[16px] font-extrabold text-[#0F2E23]">List Pesanan Dine In</p>
          <p class="text-[12px] text-gray-500 mt-0.5">Daftar pesanan aktif konsumen yang belum diselesaikan pembayarannya di meja</p>
        </div>
        <span class="mono text-[12px] text-gray-500">
          <span class="font-black text-[#0F2E23]" x-text="openBills.length"></span> Pesanan Aktif
        </span>
      </div>

      {{-- Empty state --}}
      <template x-if="openBills.length === 0">
        <div class="bg-white rounded-3xl border border-gray-200/80 p-12 text-center shadow-xs max-w-md mx-auto my-8">
          <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-2xl">
            <i class="fa-solid fa-circle-check"></i>
          </div>
          <h3 class="text-base font-extrabold text-gray-900">Tidak Ada List Pesanan Dine In</h3>
          <p class="text-xs text-gray-500 mt-1 leading-relaxed">Semua meja saat ini kosong atau transaksi pembayaran sudah lunas.</p>
        </div>
      </template>

      {{-- List Open Bills --}}
      <div x-show="openBills.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="bill in openBills" :key="bill.id">
          <div class="bg-white border border-gray-200/80 rounded-3xl p-5 h-[410px] flex flex-col justify-between shadow-xs hover:border-[#0F2E23]/40 transition-all">
            
            {{-- Header: Customer Name & Highlight Meja Badge --}}
            <div class="flex items-start justify-between gap-2 pb-3 border-b border-gray-100 shrink-0">
              <div class="space-y-0.5 min-w-0">
                <p class="font-extrabold text-sm text-gray-900 truncate leading-snug" x-text="bill.nama_konsumen"></p>
                <p class="text-[11px] font-mono text-gray-400 font-bold" x-text="'#' + (bill.kode_pesanan || ('DIN-' + bill.id))"></p>
              </div>
              
              {{-- HIGHLIGHT MEJA BADGE --}}
              <span class="px-3 py-1 bg-[#0F2E23] text-emerald-300 font-black text-xs rounded-xl shadow-xs flex items-center gap-1.5 shrink-0 border border-emerald-900/50">
                <i class="fa-solid fa-chair text-[10px]"></i>
                <span x-text="bill.meja ? (bill.meja.nomor_meja.startsWith('Meja') ? bill.meja.nomor_meja : 'Meja ' + bill.meja.nomor_meja) : 'Meja -'"></span>
              </span>
            </div>

            {{-- Items List breakdown (Flex-1 with overflow-y-auto for 100% consistent card height) --}}
            <div class="space-y-1.5 flex-1 my-2.5 min-h-0 flex flex-col">
              <p class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wider shrink-0">Rincian Pesanan:</p>
              <div class="bg-slate-50/80 border border-slate-100 rounded-2xl p-3 space-y-2 text-xs flex-1 overflow-y-auto">
                <template x-for="item in (bill.items || [])" :key="item.id">
                  <div class="flex items-center justify-between gap-2 p-1 rounded-lg">
                    <div class="flex items-center gap-2 min-w-0 pr-1">
                      <span class="font-black text-xs shrink-0 text-[#0F2E23]" x-text="item.qty + 'x'"></span>
                      <span class="font-extrabold text-xs truncate text-gray-800" x-text="item.menu ? item.menu.nama : (item.nama_menu || 'Menu')"></span>
                    </div>
                    <span class="font-bold text-[11px] shrink-0 text-gray-900" 
                          x-text="'Rp ' + formatPrice((item.menu ? item.menu.harga : (item.harga_satuan || 0)) * item.qty)"></span>
                  </div>
                </template>
              </div>
            </div>

            {{-- Status Pesanan (Dapur) & Status Pembayaran --}}
            <div class="space-y-2 pt-2 border-t border-gray-100 shrink-0">
              
              {{-- Status Pembayaran --}}
              <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-gray-500">Status Pembayaran:</span>
                <span class="px-2.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg font-extrabold text-[11px]">
                  Menunggu Bayar
                </span>
              </div>

              {{-- Total Tagihan --}}
              <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-100">
                <span class="font-extrabold text-gray-600">Total Tagihan:</span>
                <span class="font-black text-base text-[#0F2E23]"
                      x-text="'Rp ' + formatPrice((bill.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0))"></span>
              </div>
            </div>

            {{-- Action Button --}}
            <div class="pt-2 shrink-0 flex items-center gap-1.5">
              <!-- Struk Meja -->
              <a :href="'/pos/dinein/pesanan/' + bill.id + '/print-meja'" target="_blank" 
                 title="Cetak Struk Meja" 
                 class="flex-1 flex items-center justify-center py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs">
                <i class="fa-solid fa-receipt text-sm"></i>
              </a>
              <!-- Checker Dapur -->
              <a :href="'/pos/dinein/pesanan/' + bill.id + '/print-dapur'" target="_blank" 
                 title="Cetak Checker Dapur" 
                 class="flex-1 flex items-center justify-center py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs">
                <i class="fa-solid fa-fire-burner text-sm"></i>
              </a>
              <!-- Struk Konsumen -->
              <a :href="'/pos/dinein/pesanan/' + bill.id + '/print-nota'" target="_blank" 
                 title="Cetak Struk Konsumen" 
                 class="flex-1 flex items-center justify-center py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs">
                <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
              </a>
              
              <!-- Tombol Bayar -->
              <button type="button" @click="proceedToCheckout(bill)"
                 class="flex-[2.5] py-2.5 bg-[#0F2E23] hover:bg-[#0a1f17] active:scale-[0.99] text-white rounded-xl text-xs font-black text-center transition-all flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                <span>BAYAR</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
              </button>
            </div>

          </div>
        </template>
      </div>

    </div>

    {{-- ══════════════════════ VIEW 4 · RIWAYAT TRANSAKSI (SIMPLE & MINIMAL) ══════════════════════ --}}
    <div x-show="leftView === 'riwayat'" class="flex-1 overflow-y-auto p-4 md:p-6 pb-8 bg-[#f5f5f0] space-y-4">

      {{-- ── 2 SUMMARY CARDS: TOTAL TRANSAKSI & TOTAL PENDAPATAN ── --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Card 1: Total Transaksi --}}
        <div class="bg-white border border-gray-200/80 rounded-2xl p-4 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-extrabold text-gray-400 uppercase tracking-wider">Total Transaksi</p>
            <p class="text-xl font-black text-slate-900 mt-1" x-text="filteredRiwayat.length + ' Transaksi'"></p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
            <i class="fa-solid fa-receipt"></i>
          </div>
        </div>

        {{-- Card 2: Total Pendapatan --}}
        <div class="bg-white border border-gray-200/80 rounded-2xl p-4 shadow-2xs flex items-center justify-between">
          <div>
            <p class="text-xs font-extrabold text-emerald-800 uppercase tracking-wider">Total Pendapatan</p>
            <p class="text-xl font-black text-[#0F2E23] mt-1" x-text="'Rp ' + formatPrice(summaryOmzet)"></p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg">
            <i class="fa-solid fa-wallet"></i>
          </div>
        </div>
      </div>

      {{-- Section Title --}}
      <div class="flex items-center justify-between pt-1">
        <div>
          <h2 class="text-base font-extrabold text-[#0F2E23]">Daftar Riwayat Pesanan</h2>
          <p class="text-xs text-gray-500 font-medium">Riwayat transaksi pesanan lunas dan audit trail kasir</p>
        </div>
      </div>

      {{-- Empty state --}}
      <template x-if="filteredRiwayat.length === 0">
        <div class="bg-white rounded-3xl border border-gray-200/80 p-12 text-center shadow-xs max-w-md mx-auto my-8">
          <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
            <i class="fa-solid fa-receipt"></i>
          </div>
          <h3 class="text-sm font-extrabold text-gray-900">Belum ada riwayat transaksi</h3>
          <p class="text-xs text-gray-500 mt-1 leading-relaxed">Sesuai filter yang dipilih, transaksi belum ditemukan.</p>
        </div>
      </template>

      {{-- TABEL DATA RIWAYAT TRANSAKSI (MINIMALIST & CLEAN) --}}
      <div x-show="filteredRiwayat.length > 0" class="bg-white border border-gray-200/90 rounded-3xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50/80 border-b border-gray-200/80 text-gray-500 font-extrabold uppercase text-[11px] tracking-wider">
                <th class="py-3 px-4">No. Order</th>
                <th class="py-3 px-4">Waktu</th>
                <th class="py-3 px-4">Kasir</th>
                <th class="py-3 px-4">Pelanggan</th>
                <th class="py-3 px-4">Meja</th>
                <th class="py-3 px-4">Items</th>
                <th class="py-3 px-4 text-right">Total Tagihan</th>
                <th class="py-3 px-4">Metode Bayar</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
              <template x-for="trx in filteredRiwayat" :key="trx.id">
                <tr class="hover:bg-slate-50/70 transition-colors" :class="trx.status === 'void' ? 'bg-red-50/30' : ''">
                  
                  {{-- No Order --}}
                  <td class="py-3.5 px-4 font-mono font-extrabold text-slate-900">
                    <button type="button" @click="openDetailModal(trx)"
                            class="hover:text-emerald-800 underline text-xs">
                      <span x-text="'#' + (trx.kode_pesanan || ('DIN-' + trx.id))"></span>
                    </button>
                  </td>

                  {{-- Waktu --}}
                  <td class="py-3.5 px-4 text-gray-500 text-[11px]">
                    <span class="block font-bold text-gray-800" x-text="trx.created_at ? (new Date(trx.created_at)).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-'"></span>
                    <span class="text-[10px] text-gray-400" x-text="trx.created_at ? (new Date(trx.created_at)).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : ''"></span>
                  </td>

                  {{-- Kasir --}}
                  <td class="py-3.5 px-4 text-xs font-bold text-gray-700">
                    <span x-text="(trx.pembayaran && trx.pembayaran.diproses_oleh && trx.pembayaran.diproses_oleh.name) ? trx.pembayaran.diproses_oleh.name : ((trx.kasir && trx.kasir.name) ? trx.kasir.name : 'Kasir')"></span>
                  </td>

                  {{-- Pelanggan --}}
                  <td class="py-3.5 px-4 font-extrabold text-slate-800 truncate max-w-[140px]" x-text="trx.nama_konsumen"></td>

                  {{-- Meja --}}
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-800 font-extrabold text-[11px] rounded-xl"
                          x-text="trx.meja ? (trx.meja.nomor_meja.startsWith('Meja') ? trx.meja.nomor_meja : 'Meja ' + trx.meja.nomor_meja) : 'Meja -'"></span>
                  </td>

                  {{-- Jumlah Item --}}
                  <td class="py-3.5 px-4 text-xs font-bold text-gray-600">
                    <span x-text="(trx.items ? trx.items.reduce((s, i) => s + i.qty, 0) : 0) + ' Item'"></span>
                  </td>

                  {{-- Total Tagihan --}}
                  <td class="py-3.5 px-4 text-right font-black text-[#0F2E23] text-sm"
                      x-text="'Rp ' + formatPrice((trx.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0))"></td>

                  {{-- Metode Bayar --}}
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-lg font-extrabold text-[10px] uppercase tracking-wider"
                          :class="(trx.pembayaran && trx.pembayaran.metode_bayar === 'cash') ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-blue-50 text-blue-900 border border-blue-200'"
                          x-text="trx.pembayaran ? trx.pembayaran.metode_bayar : 'LUNAS'"></span>
                  </td>

                  {{-- Status (Minimalist Dot) --}}
                  <td class="py-3.5 px-4 text-center">
                    <template x-if="trx.status === 'lunas'">
                      <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full font-extrabold text-[11px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Lunas
                      </span>
                    </template>
                    <template x-if="trx.status === 'menunggu_pembayaran'">
                      <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-full font-extrabold text-[11px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Pending
                      </span>
                    </template>
                    <template x-if="trx.status === 'void'">
                      <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-red-50 text-red-800 border border-red-200 rounded-full font-extrabold text-[11px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>Void
                      </span>
                    </template>
                  </td>

                  {{-- Aksi (Clean Minimal Buttons) --}}
                  <td class="py-3.5 px-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                      <a :href="'/pos/dinein/receipts/' + trx.id" target="_blank"
                         title="Struk Nota"
                         class="w-7 h-7 bg-slate-100 hover:bg-[#0F2E23] hover:text-white text-slate-700 rounded-lg text-xs flex items-center justify-center transition-all">
                        <i class="fa-solid fa-print"></i>
                      </a>
                      <button type="button" @click="openDetailModal(trx)"
                              title="Detail Transaksi"
                              class="w-7 h-7 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs flex items-center justify-center transition-all">
                        <i class="fa-solid fa-eye"></i>
                      </button>
                      <template x-if="trx.status !== 'void'">
                        <button type="button" @click="openVoidModal(trx)"
                                title="Void Transaksi"
                                class="w-7 h-7 bg-red-50 hover:bg-red-600 hover:text-white text-red-700 rounded-lg text-xs flex items-center justify-center transition-all">
                          <i class="fa-solid fa-ban"></i>
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ══════════════════════ VIEW 5 · QR SCAN MENU (KARTU MEJA) ══════════════════════ --}}
    <div x-show="leftView === 'qr'" class="flex-1 overflow-y-auto p-4 md:p-6 pb-8 bg-[#f5f5f0] space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-extrabold text-[#0F2E23]">QR Scan Menu (Kartu Meja Digital)</h2>
          <p class="text-xs text-gray-500 font-medium">Kartu QR Code Meja untuk pemesanan mandiri oleh pelanggan Saung Babakan Cinta</p>
        </div>
      </div>

      {{-- Grid QR Cards --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 justify-items-center">
        @forelse($mejas as $m)
          @php
            $qrTargetUrl = route('qr.menu', ['meja' => $m->id]);
            $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=0&data=" . urlencode($qrTargetUrl);
            $logoUrl = asset('images/logo-saung.png');
            $cleanNomorMeja = trim(preg_replace('/^meja\s*/i', '', $m->nomor_meja));
          @endphp
          
          <div class="card-qr-stand w-full max-w-[300px] aspect-[1/1.55] rounded-3xl overflow-hidden shadow-xl border-4 border-emerald-500/30 flex flex-col justify-between p-5 relative text-white selection:bg-transparent"
               style="background: linear-gradient(145deg, #0F2E23 0%, #164032 50%, #0A2219 100%);">
              
              <!-- Dark Overlay -->
              <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/40 pointer-events-none"></div>

              <!-- Decorative Corners -->
              <div class="absolute top-3 left-3 w-4 h-4 border-t-2 border-l-2 border-amber-400/60 rounded-tl-lg"></div>
              <div class="absolute top-3 right-3 w-4 h-4 border-t-2 border-r-2 border-amber-400/60 rounded-tr-lg"></div>
              <div class="absolute bottom-3 left-3 w-4 h-4 border-b-2 border-l-2 border-amber-400/60 rounded-bl-lg"></div>
              <div class="absolute bottom-3 right-3 w-4 h-4 border-b-2 border-r-2 border-amber-400/60 rounded-br-lg"></div>

              <!-- Header -->
              <div class="relative z-10 text-center pt-1 space-y-0.5">
                  <h2 class="text-2xl font-black uppercase tracking-wider text-amber-400 drop-shadow-md leading-none">
                      SCAN MENU
                  </h2>
                  <div class="pt-2">
                      <span class="inline-flex items-center gap-1.5 px-3.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md text-white border border-amber-400/40 text-[12px] font-extrabold shadow-sm">
                          <i class="fa-solid fa-chair text-[10px] text-amber-400"></i> Meja {{ $cleanNomorMeja }}
                      </span>
                  </div>
              </div>

              <!-- QR Code Frame -->
              <div class="relative z-10 my-auto py-1 flex flex-col items-center">
                  <div class="bg-white rounded-3xl p-3.5 shadow-2xl border-4 border-amber-400/50 relative flex items-center justify-center transform transition-transform hover:scale-[1.02]">
                      <img src="{{ $qrApiUrl }}" alt="QR Code Meja {{ $m->nomor_meja }}" class="w-44 h-44 object-contain rounded-xl">
                      <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                          <div class="w-11 h-11 rounded-full bg-white p-1 shadow-xl border-2 border-emerald-800 flex items-center justify-center overflow-hidden">
                              <img src="{{ $logoUrl }}" alt="Logo Saung" class="w-full h-full object-contain">
                          </div>
                      </div>
                  </div>
                  <div class="mt-3 text-center">
                      <p class="text-[11px] font-bold text-white tracking-wide">Scan QR Code untuk melihat menu</p>
                      <p class="text-[9px] font-medium text-amber-300 mt-0.5">Arahkan kamera HP Anda memesan langsung</p>
                  </div>
              </div>

              <!-- Footer -->
              <div class="relative z-10 text-center pb-1 pt-1.5 border-t border-amber-400/30 flex items-center justify-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-white/10 backdrop-blur-md p-1 flex items-center justify-center border border-amber-400/40 shrink-0">
                      <img src="{{ $logoUrl }}" alt="Logo Saung" class="w-full h-full object-contain">
                  </div>
                  <div class="text-left">
                      <h3 class="text-[11px] font-black tracking-wider text-white uppercase leading-none">SAUNG BABAKAN CINTA</h3>
                      <span class="text-[8px] font-semibold text-amber-300 block leading-tight mt-0.5">Rumah Makan Khas Sunda</span>
                  </div>
              </div>

          </div>
        @empty
          <div class="col-span-full py-16 text-center text-gray-400 bg-white rounded-3xl border border-gray-200 w-full shadow-xs">
              <i class="fa-solid fa-qrcode text-3xl mb-2 text-gray-300"></i>
              <p class="text-sm font-semibold text-gray-700">Belum ada data meja.</p>
          </div>
        @endforelse
      </div>
    </div>

  </div>

  {{-- ─────────────────────────────── RIGHT PANEL: CART & EMBEDDED RECEIPT ────────────────────────────── --}}
  <div x-show="leftView === 'menu'" class="w-[380px] xl:w-[420px] bg-white border-l border-gray-200/80 flex flex-col justify-between shrink-0 shadow-xs">
    
    {{-- MODE A: CART INPUT & ITEM LIST --}}
    <template x-if="rightPanelMode === 'cart'">
      <div class="flex flex-col h-full justify-between">
        {{-- Header & Customer Input --}}
        <div class="p-5 border-b border-gray-100 space-y-3 shrink-0">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-extrabold text-[#0F2E23]">Detail Pesanan</h2>
          </div>

          <div class="space-y-2 pt-1">
            {{-- Input 1: Pilih Meja (Custom Dropdown) --}}
            <div class="relative" @click.outside="showTableDropdown = false">
              <button type="button" @click.stop="showTableDropdown = !showTableDropdown"
                      class="w-full h-10 px-3.5 text-xs font-medium rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/10 outline-none transition text-left flex items-center justify-between cursor-pointer">
                <span :class="selectedTable ? 'text-gray-900 font-extrabold' : 'text-gray-400'"
                      x-text="selectedTableLabel ? (selectedTableLabel.startsWith('Meja') ? selectedTableLabel : 'Meja ' + selectedTableLabel) : 'Pilih Meja Resto *'"></span>
                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="showTableDropdown ? 'rotate-180' : ''"></i>
              </button>

              {{-- Custom Dropdown Panel --}}
              <div x-show="showTableDropdown" x-transition.opacity style="display: none;"
                   class="absolute left-0 right-0 top-11 bg-white rounded-2xl border border-gray-200/90 shadow-2xl z-50 p-2 space-y-1 max-h-60 overflow-y-auto">
                
                <div class="px-2 py-1 flex items-center justify-between border-b border-gray-100 mb-1">
                  <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Pilih Meja Resto</span>
                  <button type="button" @click.stop="leftView = 'meja'; showTableDropdown = false" class="text-[10px] font-bold text-emerald-800 hover:underline">
                    Denah Meja ➔
                  </button>
                </div>

                <template x-for="m in mejasList" :key="m.id">
                  <button type="button"
                          @click.stop="if (m.status === 'kosong') { selectTable(m.id, m.nomor_meja); showTableDropdown = false }"
                          :disabled="m.status !== 'kosong'"
                          :class="m.status !== 'kosong' ? 'opacity-40 bg-gray-100/70 text-gray-400 cursor-not-allowed border-transparent' : (selectedTable == m.id ? 'bg-emerald-50 text-[#0F2E23] font-black border-emerald-200 cursor-pointer' : 'hover:bg-gray-50 text-gray-700 font-bold border-transparent cursor-pointer')"
                          class="w-full text-left px-3 py-2 text-xs rounded-xl border flex items-center justify-between transition-all">
                    <span x-text="m.nomor_meja.startsWith('Meja') ? m.nomor_meja : 'Meja ' + m.nomor_meja"></span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                          :class="m.status === 'kosong' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900 border border-amber-200'"
                          x-text="m.status === 'kosong' ? 'Kosong' : 'Terisi (Penuh)'"></span>
                  </button>
                </template>
              </div>
            </div>

            {{-- Input 2: Nama Pelanggan --}}
            <input type="text" x-model="customerName" placeholder="Nama Pelanggan / Konsumen *"
                   class="w-full h-10 px-3.5 text-xs font-medium rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/10 outline-none transition">

            {{-- Input 3: No HP --}}
            <input type="text" x-model="customerPhone" placeholder="No. HP / Telepon (Opsional)"
                   class="w-full h-10 px-3.5 text-xs font-medium rounded-xl border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-[#0F2E23] focus:ring-2 focus:ring-[#0F2E23]/10 outline-none transition">
          </div>
        </div>

        {{-- Cart Items List --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 divide-y divide-gray-100">
          <template x-if="cart.length === 0">
            <div class="h-full flex flex-col items-center justify-center py-12 text-center text-gray-400 space-y-2">
              <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-xl">
                <i class="fa-solid fa-basket-shopping"></i>
              </div>
              <p class="text-xs font-bold">Keranjang Masih Kosong</p>
              <p class="text-[11px] text-gray-400 max-w-[200px]">Pilih menu makanan / minuman dari katalog di sebelah kiri.</p>
            </div>
          </template>

          <template x-for="(item, index) in cart" :key="item.menu_id">
            <div class="pt-3 first:pt-0 space-y-1.5">
              <div class="flex items-start justify-between gap-2">
                <span class="font-extrabold text-sm text-gray-900 flex-1 leading-snug" x-text="item.nama"></span>
                <span class="font-black text-sm text-[#0F2E23]" x-text="'Rp ' + formatPrice(item.harga * item.qty)"></span>
              </div>

              <div class="flex items-center justify-between pt-1">
                <input type="text" x-model="item.catatan" placeholder="Catatan khusus…"
                       class="h-7 text-[11px] px-2.5 rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-[#0F2E23] outline-none w-44">

                <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl">
                  <button @click="updateQty(index, -1)" class="w-6 h-6 rounded-lg bg-white text-gray-700 font-extrabold text-xs flex items-center justify-center hover:bg-gray-200 transition-colors">-</button>
                  <span class="w-6 text-center text-xs font-black text-gray-900" x-text="item.qty"></span>
                  <button @click="addToCart(item.menu_id, item.nama, item.harga)" class="w-6 h-6 rounded-lg bg-white text-gray-700 font-extrabold text-xs flex items-center justify-center hover:bg-gray-200 transition-colors">+</button>
                </div>
              </div>
            </div>
          </template>
        </div>

        {{-- Bottom Summary & Action Bar --}}
        <div class="p-5 border-t border-gray-200/80 bg-gray-50/60 space-y-3 shrink-0">
          <div class="space-y-1 text-xs font-medium text-gray-500">
            <div class="flex justify-between">
              <span>Total Item</span>
              <span class="font-bold text-gray-900" x-text="totalQty + ' Item'"></span>
            </div>
            <div class="flex justify-between text-sm pt-1 border-t border-gray-200/60">
              <span class="font-bold text-gray-700">Subtotal</span>
              <span class="font-black text-base text-[#0F2E23]" x-text="'Rp ' + formatPrice(totalPrice)"></span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 pt-1">
            <button type="button" @click.stop="submitOrder('simpan')" :disabled="isSubmitting"
                    class="py-3.5 px-3 rounded-2xl border border-gray-300 bg-white hover:bg-gray-100 text-gray-800 font-extrabold text-xs transition-all shadow-2xs cursor-pointer">
              SIMPAN
            </button>
            <button type="button" @click.stop="submitOrder('bayar')" :disabled="isSubmitting"
                    class="py-3.5 px-3 rounded-2xl bg-[#0F2E23] hover:bg-[#0a1f17] active:scale-[0.99] text-white font-extrabold text-xs transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer">
              <span>BAYAR</span>
              <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
          </div>
        </div>
      </div>
    </template>

    {{-- MODE B: EMBEDDED RECEIPT (STRUK DAPUR & MEJA DITAMPILKAN DI SINI) --}}
    <template x-if="rightPanelMode === 'receipt'">
      <div class="flex flex-col h-full justify-between">
        {{-- Header Status Banner --}}
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 via-teal-50/50 to-white flex items-center justify-between shrink-0 shadow-2xs">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-2xl bg-[#0F2E23] text-emerald-400 flex items-center justify-center text-sm font-black shadow-xs ring-4 ring-emerald-500/10">
              <i class="fa-solid fa-check"></i>
            </div>
            <div>
              <div class="flex items-center gap-1.5">
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">PESANAN DISIMPAN</h2>
                <span class="px-2 py-0.5 rounded-full bg-[#0F2E23] text-emerald-300 font-black text-[10px] tracking-wide" x-text="savedPesananObject ? ('#' + (savedPesananObject.kode_pesanan || ('DIN-' + savedPesananObject.id))) : ''"></span>
              </div>
              <p class="text-[11px] font-bold text-emerald-800 mt-0.5" x-text="savedPesananObject ? ((savedPesananObject.meja ? (savedPesananObject.meja.nomor_meja.startsWith('Meja') ? savedPesananObject.meja.nomor_meja : 'Meja ' + savedPesananObject.meja.nomor_meja) : 'Meja -') + ' • ' + savedPesananObject.nama_konsumen) : ''"></p>
            </div>
          </div>

          <button type="button" @click="resetCartPanel()" class="text-xs font-extrabold text-[#0F2E23] hover:bg-emerald-100 bg-white px-3 py-1.5 rounded-xl border border-emerald-200/90 shadow-2xs transition-all active:scale-95 cursor-pointer">
            + Pesanan Baru
          </button>
        </div>

        {{-- Receipt View Filter Tabs --}}
        <div class="px-4 py-2 bg-gray-50 border-b border-gray-200/60 flex items-center gap-1 shrink-0">
          <button type="button" @click="receiptTab = 'all'"
                  :class="receiptTab === 'all' ? 'bg-[#0F2E23] text-white shadow-2xs font-extrabold' : 'bg-white text-gray-600 font-bold hover:bg-gray-100 border border-gray-200/80'"
                  class="flex-1 py-1.5 text-[11px] rounded-xl transition-all text-center cursor-pointer">
            Semua (Gabungan)
          </button>
          <button type="button" @click="receiptTab = 'dapur'"
                  :class="receiptTab === 'dapur' ? 'bg-[#0F2E23] text-white shadow-2xs font-extrabold' : 'bg-white text-gray-600 font-bold hover:bg-gray-100 border border-gray-200/80'"
                  class="flex-1 py-1.5 text-[11px] rounded-xl transition-all text-center cursor-pointer">
            Dapur
          </button>
          <button type="button" @click="receiptTab = 'meja'"
                  :class="receiptTab === 'meja' ? 'bg-[#0F2E23] text-white shadow-2xs font-extrabold' : 'bg-white text-gray-600 font-bold hover:bg-gray-100 border border-gray-200/80'"
                  class="flex-1 py-1.5 text-[11px] rounded-xl transition-all text-center cursor-pointer">
            Meja
          </button>
        </div>

        {{-- Scrollable Receipt Cards Body --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-100/70 font-mono text-xs text-gray-800">
          
          {{-- Section 1: Checker Dapur --}}
          <template x-if="receiptTab === 'all' || receiptTab === 'dapur'">
            <div class="bg-white rounded-3xl p-5 border border-gray-200/90 shadow-sm space-y-3 relative overflow-hidden">
              <div class="text-center space-y-1">
                <div class="font-black text-sm text-slate-900 tracking-wider font-sans">SAUNG BABAKAN CINTA</div>
                <div class="inline-block px-3 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-bold text-[10px] uppercase tracking-wider font-sans">
                  ** CHECKER DAPUR (KOT) **
                </div>
              </div>
              
              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200/60 space-y-1 text-[11px]">
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">No. Order:</span><span class="font-bold text-gray-900" x-text="savedPesananObject ? ('#' + (savedPesananObject.kode_pesanan || ('DIN-' + savedPesananObject.id))) : '-'"></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">Meja:</span><span class="font-extrabold text-emerald-800" x-text="savedPesananObject && savedPesananObject.meja ? (savedPesananObject.meja.nomor_meja.startsWith('Meja') ? savedPesananObject.meja.nomor_meja : 'Meja ' + savedPesananObject.meja.nomor_meja) : '-'"></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">Pelanggan:</span><span class="font-bold text-gray-900" x-text="savedPesananObject ? savedPesananObject.nama_konsumen : '-'"></span></div>
              </div>

              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              {{-- Items List --}}
              <div class="space-y-2 text-[11px] pt-0.5">
                <template x-for="item in (savedPesananObject ? savedPesananObject.items : [])" :key="'panel-dapur-' + item.id">
                  <div class="p-2 rounded-xl bg-slate-50/70 border border-slate-100 space-y-1">
                    <div class="flex justify-between font-bold text-gray-900">
                      <span class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 bg-slate-200 text-slate-800 rounded-md font-extrabold text-[10px]" x-text="item.qty + 'x'"></span>
                        <span x-text="item.menu ? item.menu.nama : (item.nama_menu || 'Menu')"></span>
                      </span>
                      <span class="font-extrabold text-slate-800" x-text="'Rp ' + formatPrice((item.menu ? item.menu.harga : (item.harga_satuan || 0)) * item.qty)"></span>
                    </div>
                    <template x-if="item.catatan">
                      <p class="text-[10px] text-amber-800 font-medium italic flex items-center gap-1 pl-6">
                        <i class="fa-solid fa-sticky-note text-[9px] text-amber-600"></i>
                        <span x-text="item.catatan"></span>
                      </p>
                    </template>
                  </div>
                </template>
              </div>

              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              <div class="flex justify-between items-center font-extrabold text-xs pt-0.5">
                <span class="text-gray-600">TOTAL ITEM:</span>
                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-900 rounded-lg text-xs font-black" x-text="(savedPesananObject && savedPesananObject.items) ? savedPesananObject.items.reduce((s, i) => s + i.qty, 0) : 0"></span>
              </div>
            </div>
          </template>

          {{-- Tear Line / Divider --}}
          <template x-if="receiptTab === 'all'">
            <div class="relative my-2 py-1 flex items-center justify-center">
              <div class="absolute inset-0 flex items-center"><div class="w-full border-t-2 border-dashed border-gray-300"></div></div>
              <div class="relative px-3 py-0.5 bg-white text-gray-500 font-bold text-[10px] rounded-full border border-gray-300 shadow-2xs flex items-center gap-1.5 uppercase tracking-wider font-sans">
                <i class="fa-solid fa-scissors text-gray-400 text-[10px]"></i>
                <span>Potong Di Sini</span>
              </div>
            </div>
          </template>

          {{-- Section 2: Checker Meja --}}
          <template x-if="receiptTab === 'all' || receiptTab === 'meja'">
            <div class="bg-white rounded-3xl p-5 border border-gray-200/90 shadow-sm space-y-3 relative overflow-hidden">
              <div class="text-center space-y-1">
                <div class="font-black text-sm text-slate-900 tracking-wider font-sans">SAUNG BABAKAN CINTA</div>
                <div class="inline-block px-3 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200/80 font-bold text-[10px] uppercase tracking-wider font-sans">
                  ** CHECKER MEJA **
                </div>
              </div>
              
              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200/60 space-y-1 text-[11px]">
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">No. Order:</span><span class="font-bold text-gray-900" x-text="savedPesananObject ? ('#' + (savedPesananObject.kode_pesanan || ('DIN-' + savedPesananObject.id))) : '-'"></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">Meja:</span><span class="font-extrabold text-emerald-800" x-text="savedPesananObject && savedPesananObject.meja ? (savedPesananObject.meja.nomor_meja.startsWith('Meja') ? savedPesananObject.meja.nomor_meja : 'Meja ' + savedPesananObject.meja.nomor_meja) : '-'"></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">Pelanggan:</span><span class="font-bold text-gray-900" x-text="savedPesananObject ? savedPesananObject.nama_konsumen : '-'"></span></div>
              </div>

              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              {{-- Items List --}}
              <div class="space-y-2 text-[11px] pt-0.5">
                <template x-for="item in (savedPesananObject ? savedPesananObject.items : [])" :key="'panel-meja-' + item.id">
                  <div class="p-2 rounded-xl bg-slate-50/70 border border-slate-100 space-y-1">
                    <div class="flex justify-between font-bold text-gray-900">
                      <span class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 bg-slate-200 text-slate-800 rounded-md font-extrabold text-[10px]" x-text="item.qty + 'x'"></span>
                        <span x-text="item.menu ? item.menu.nama : (item.nama_menu || 'Menu')"></span>
                      </span>
                      <span class="font-extrabold text-slate-800" x-text="'Rp ' + formatPrice((item.menu ? item.menu.harga : (item.harga_satuan || 0)) * item.qty)"></span>
                    </div>
                    <template x-if="item.catatan">
                      <p class="text-[10px] text-amber-800 font-medium italic flex items-center gap-1 pl-6">
                        <i class="fa-solid fa-sticky-note text-[9px] text-amber-600"></i>
                        <span x-text="item.catatan"></span>
                      </p>
                    </template>
                  </div>
                </template>
              </div>

              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              <div class="flex justify-between items-center font-extrabold text-xs pt-0.5">
                <span class="text-gray-600">TOTAL ITEM:</span>
                <span class="px-2.5 py-1 bg-blue-100 text-blue-900 rounded-lg text-xs font-black" x-text="(savedPesananObject && savedPesananObject.items) ? savedPesananObject.items.reduce((s, i) => s + i.qty, 0) : 0"></span>
              </div>
            </div>
          </template>
        </div>

        {{-- Bottom Action Bar --}}
        <div class="p-4 border-t border-gray-200/80 bg-white space-y-2 shrink-0 shadow-lg">
          <button type="button"
                  @click="printSilentIframe(receiptTab === 'all' ? ('/pos/dinein/pesanan/' + savedPesananId + '/print-gabungan') : (receiptTab === 'dapur' ? ('/pos/dinein/pesanan/' + savedPesananId + '/print-dapur') : ('/pos/dinein/pesanan/' + savedPesananId + '/print-meja')))"
                  class="w-full py-3.5 px-4 bg-[#0F2E23] hover:bg-[#0a1f17] active:scale-[0.99] text-white font-extrabold rounded-2xl text-xs transition-all flex items-center justify-center gap-2 shadow-md text-center cursor-pointer">
            <i class="fa-solid fa-print text-emerald-400"></i>
            <span x-text="receiptTab === 'all' ? 'Cetak Struk Dapur & Meja (1 Halaman)' : (receiptTab === 'dapur' ? 'Cetak Struk Dapur' : 'Cetak Struk Meja')"></span>
          </button>

          <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="resetCartPanel()"
                    class="py-3 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold rounded-2xl text-xs transition-all text-center active:scale-95 cursor-pointer">
              + Pesanan Baru
            </button>
            <a :href="'/pos/dinein/meja/' + (savedPesananObject ? savedPesananObject.meja_id : '') + '/checkout'"
               class="py-3 px-3 bg-emerald-100 hover:bg-emerald-200 text-emerald-900 font-extrabold rounded-2xl text-xs transition-all flex items-center justify-center gap-1.5 text-center active:scale-95 cursor-pointer">
              <span>BAYAR</span>
              <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
          </div>
        </div>
      </div>
    </template>

  </div>

  </div>
  {{-- ── MODAL DETAIL TRANSAKSI LENGKAP ── --}}
  <div x-show="showTrxDetailModal" x-cloak x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-3xl p-6 max-w-lg w-full space-y-4 shadow-2xl border border-gray-100" @click.outside="showTrxDetailModal = false">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <div>
          <h3 class="text-base font-extrabold text-slate-900" x-text="'Detail Transaksi #' + (selectedTrxDetail ? (selectedTrxDetail.kode_pesanan || ('DIN-' + selectedTrxDetail.id)) : '')"></h3>
          <p class="text-xs text-slate-400 font-medium" x-text="selectedTrxDetail ? selectedTrxDetail.created_at : ''"></p>
        </div>
        <button type="button" @click="showTrxDetailModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
      </div>

      <template x-if="selectedTrxDetail">
        <div class="space-y-3 text-xs">
          {{-- Info Utama --}}
          <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
            <div><span class="text-slate-400 font-bold block">Pelanggan:</span> <span class="font-extrabold text-slate-900" x-text="selectedTrxDetail.nama_konsumen"></span></div>
            <div><span class="text-slate-400 font-bold block">Meja:</span> <span class="font-extrabold text-emerald-800" x-text="selectedTrxDetail.meja ? selectedTrxDetail.meja.nomor_meja : '-'"></span></div>
            <div><span class="text-slate-400 font-bold block">Kasir Bertugas:</span> <span class="font-extrabold text-slate-800" x-text="(selectedTrxDetail.pembayaran && selectedTrxDetail.pembayaran.diproses_oleh && selectedTrxDetail.pembayaran.diproses_oleh.name) ? selectedTrxDetail.pembayaran.diproses_oleh.name : 'Kasir'"></span></div>
            <div><span class="text-slate-400 font-bold block">Metode Bayar:</span> <span class="font-extrabold text-blue-800 uppercase" x-text="selectedTrxDetail.pembayaran ? selectedTrxDetail.pembayaran.metode_bayar : 'LUNAS'"></span></div>
          </div>

          {{-- Item List --}}
          <div class="space-y-1.5">
            <p class="font-extrabold text-slate-700">Rincian Menu Dipesan:</p>
            <div class="border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100 max-h-48 overflow-y-auto">
              <template x-for="item in (selectedTrxDetail.items || [])" :key="item.id">
                <div class="p-2.5 flex items-center justify-between">
                  <div>
                    <p class="font-extrabold text-slate-800" x-text="item.qty + 'x ' + (item.menu ? item.menu.nama : (item.nama_menu || 'Menu'))"></p>
                    <p class="text-[10px] text-slate-400" x-text="item.catatan ? ('Catatan: ' + item.catatan) : ''"></p>
                  </div>
                  <span class="font-extrabold text-slate-900" x-text="'Rp ' + formatPrice((item.menu ? item.menu.harga : (item.harga_satuan || 0)) * item.qty)"></span>
                </div>
              </template>
            </div>
          </div>

          {{-- Total Summary --}}
          <div class="pt-2 border-t border-slate-200 space-y-1 text-xs">
            <div class="flex justify-between font-bold text-slate-600">
              <span>Subtotal:</span>
              <span x-text="'Rp ' + formatPrice((selectedTrxDetail.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0))"></span>
            </div>
            <div class="flex justify-between font-black text-sm text-[#0F2E23] pt-1 border-t border-slate-200">
              <span>Total Tagihan:</span>
              <span x-text="'Rp ' + formatPrice((selectedTrxDetail.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0))"></span>
            </div>
          </div>
        </div>
      </template>

      <div class="pt-4 flex flex-wrap justify-between gap-2 border-t border-gray-100">
        <div class="flex gap-2">
            <button type="button" @click="printSilentIframe('/pos/dinein/pesanan/' + selectedTrxDetail.id + '/print-dapur')" class="px-4 py-2.5 bg-emerald-50 text-emerald-800 border border-emerald-200 font-extrabold rounded-xl text-xs hover:bg-emerald-100 transition-colors">
                <i class="fa-solid fa-print"></i> Struk Dapur
            </button>
            <button type="button" @click="printSilentIframe('/pos/dinein/pesanan/' + selectedTrxDetail.id + '/print-meja')" class="px-4 py-2.5 bg-blue-50 text-blue-800 border border-blue-200 font-extrabold rounded-xl text-xs hover:bg-blue-100 transition-colors">
                <i class="fa-solid fa-print"></i> Struk Meja
            </button>
        </div>
        <button type="button" @click="showTrxDetailModal = false" class="px-5 py-2.5 bg-slate-900 text-white font-extrabold rounded-xl text-xs hover:bg-slate-800 transition-colors cursor-pointer">
          Tutup
        </button>
      </div>
    </div>
  </div>

  {{-- ── MODAL VOID / BATAL TRANSAKSI ── --}}
  <div x-show="showVoidModal" x-cloak x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-3xl p-6 max-w-md w-full space-y-4 shadow-2xl border border-gray-100" @click.outside="showVoidModal = false">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-red-100 text-red-800 flex items-center justify-center font-bold text-sm">
            <i class="fa-solid fa-ban"></i>
          </div>
          <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Void Transaksi</h3>
        </div>
        <button type="button" @click="showVoidModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
      </div>

      <div class="space-y-3 text-xs">
        <p class="text-slate-600 font-medium">Anda akan membatalkan (Void) pesanan <strong x-text="trxToVoid ? ('#' + (trxToVoid.kode_pesanan || ('DIN-' + trxToVoid.id))) : ''"></strong>. Transaksi tidak akan dihapus dan tetap tercatat untuk audit trail.</p>

        <div class="space-y-1.5">
          <label class="font-extrabold text-slate-700">Pilih Alasan Void <span class="text-red-500">*</span></label>
          <select x-model="alasanVoidInput" class="w-full h-11 px-3.5 text-xs font-extrabold rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-[#0F2E23] outline-none">
            <option value="Salah Input Menu">Salah Input Menu</option>
            <option value="Request Pembatalan Pelanggan">Request Pembatalan Pelanggan</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>

        <div class="space-y-1.5 mt-3">
          <label class="font-extrabold text-slate-700">Catatan Tambahan</label>
          <input type="text" x-model="catatanVoidInput" class="w-full h-11 px-3.5 text-xs font-extrabold rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-[#0F2E23] outline-none" placeholder="Misal: Pesanan ganda...">
        </div>

        <div class="mt-6 flex gap-3">
          <button type="button" @click="showVoidModal = false" class="flex-1 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold rounded-2xl text-xs transition-colors">
            Tutup
          </button>
          <button type="button" @click="submitVoidOrder" :disabled="isSubmitting" class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-2xl text-xs transition-colors shadow-sm disabled:opacity-50">
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
    <div class="bg-white rounded-[24px] p-6 max-w-sm w-full shadow-2xl relative">
      
      {{-- Modal Header --}}
      <div class="text-center mb-6 relative">
        <h3 class="text-[17px] font-bold text-[#111827]">Cetak Struk</h3>
        <p class="text-[13px] font-medium text-gray-500 mt-0.5">Pilih struk yang akan dicetak</p>
        <button type="button" @click="closePrintModal()" class="absolute right-0 top-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100/80 hover:bg-gray-200 text-gray-500 transition-colors">
          <i class="fa-solid fa-xmark text-sm"></i>
        </button>
      </div>

      {{-- List Options (Checkboxes) --}}
      <div class="space-y-3">
        
        {{-- Option 1: Struk Pelanggan --}}
        <label class="w-full group bg-white border rounded-2xl p-4 flex items-center justify-between transition-all cursor-pointer select-none"
               :class="selectedPrintOptions.includes('konsumen') ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-200 hover:border-emerald-200'">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-sm border"
                     :class="selectedPrintOptions.includes('konsumen') ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-gray-50 text-gray-500 border-gray-100 group-hover:bg-white'">
                    <i class="ph ph-receipt text-xl"></i>
                </div>
                <span class="font-semibold text-sm transition-colors"
                      :class="selectedPrintOptions.includes('konsumen') ? 'text-emerald-800' : 'text-gray-700'">Struk Pelanggan</span>
            </div>
            <input type="checkbox" class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" 
                   value="konsumen" x-model="selectedPrintOptions">
        </label>

        {{-- Option 2: Struk Dapur --}}
        <label class="w-full group bg-white border rounded-2xl p-4 flex items-center justify-between transition-all cursor-pointer select-none"
               :class="selectedPrintOptions.includes('dapur') ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-200 hover:border-emerald-200'">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-sm border"
                     :class="selectedPrintOptions.includes('dapur') ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-gray-50 text-gray-500 border-gray-100 group-hover:bg-white'">
                    <i class="ph ph-cooking-pot text-xl"></i>
                </div>
                <span class="font-semibold text-sm transition-colors"
                      :class="selectedPrintOptions.includes('dapur') ? 'text-emerald-800' : 'text-gray-700'">Struk Dapur</span>
            </div>
            <input type="checkbox" class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer" 
                   value="dapur" x-model="selectedPrintOptions">
        </label>

        {{-- Option 3: Struk Checker Pesanan --}}
        <label class="w-full group bg-white border rounded-2xl p-4 flex items-center justify-between transition-all cursor-pointer select-none"
               :class="selectedPrintOptions.includes('meja') ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-200 hover:border-emerald-200'">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-sm border"
                     :class="selectedPrintOptions.includes('meja') ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-gray-50 text-gray-500 border-gray-100 group-hover:bg-white'">
                    <i class="ph ph-clipboard-text text-xl"></i>
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
        <button type="button" @click="executePrintSelection()" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-sm transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
          <i class="fa-solid fa-print"></i>
          Cetak yang Dipilih
        </button>

        <button type="button" @click="closePrintModal()" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-2xl text-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
          <span x-text="pendingCheckoutAction ? 'Lewati & Proses Pembayaran' : 'Lewati (Kembali)'"></span>
          <i class="fa-solid fa-arrow-right text-[11px]"></i>
        </button>
      </div>
      
    </div>
  </div>

</div>
@endsection
