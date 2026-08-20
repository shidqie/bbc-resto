@extends('layouts.pos')

@push('scripts')
<script src="{{ asset('js/qrcode.min.js') }}"></script>
@endpush

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
  .mono            { font-family:'Anonymous Pro', monospace; letter-spacing:.05em; }
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
      this.selectedTrxDetail = trx;
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
        ['No. Order', 'Pelanggan', 'Meja', 'Waktu', 'Kasir', 'Metode Bayar', 'Status', 'Total Tagihan (Rp)']
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
    showSavePrintModal: false,
    showSuccessModal: false,
    savedPesananId: null,
    savedPesananObject: null,
    activePrintEmbed: 'gabungan',

    // Pengaturan Biaya Layanan Flat Nominal per Transaksi/Struk
    pajakAktif: false,
    persentasePajak: 0,
    layananAktif: {{ ($pengaturanTransaksi->layanan_aktif ?? true) ? 'true' : 'false' }},
    nominalLayanan: {{ (float) ($pengaturanTransaksi->nominal_layanan ?? 1000) }},

    openBills: @json($openBills),

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
        if (!bill.items || bill.items.length === 0) return;
        window.location.href = '/pos/dinein/meja/' + bill.id + '/checkout';
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

    async konfirmasiPesanan(bill) {
        try {
            const res = await fetch(`/pos/dinein/pesanan/${bill.id}/konfirmasi`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                // Cetak Struk Gabungan (Meja & Dapur) otomatis
                this.printSilentIframe('/pos/dinein/pesanan/' + bill.id + '/print-gabungan');
                
                // Segarkan data tabel dan status
                this.fetchTableStatus();
                
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 }).fire({
                    icon: 'success',
                    title: 'Pesanan Dikonfirmasi'
                });
            } else {
                Swal.fire('Gagal', data.message || 'Gagal konfirmasi pesanan.', 'error');
            }
        } catch (e) {
            console.error(e);
            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        }
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

<div x-data="posSystem" x-init="startTableStatusPolling()" class="pos-root min-h-[calc(100vh-4rem)] lg:h-[calc(100vh-4rem)] w-full flex flex-col lg:flex-row lg:overflow-hidden bg-secondary-soft text-body">

  {{-- ─────────────────────────────── LEFT PANEL ────────────────────────────── --}}
  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">

    {{-- ── TOPBAR ──────────────────────────────────────────────────── --}}
    <header class="bg-white px-6 py-3.5 shrink-0 z-10 space-y-3">

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

          {{-- Tab 2: Daftar Pesanan Dine In --}}
          <x-ui.tab @click="leftView = 'open_bills'"
                    x-bind:class="leftView === 'open_bills' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-primary hover:border-primary/40'">
            <span class="inline-flex items-center gap-2">
              <span>Daftar Pesanan Dine In</span>
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
        <div class="relative w-full md:w-64 shrink-0" x-show="leftView !== 'qr'" x-cloak>
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
        <div class="flex-1 overflow-hidden">
          <div x-show="leftView === 'menu'" x-cloak class="flex overflow-x-auto no-scrollbar gap-2">
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

          <div x-show="leftView === 'open_bills'" class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            
            {{-- Dropdown Filter: Metode --}}
            <div x-data="{ open: false }" class="relative shrink-0" @click.away="open = false">
              <button type="button" @click="open = !open" 
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 focus:outline-none transition-colors">
                <span>Metode</span>
                <span x-show="openBillFilter !== 'semua'" class="flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-emerald-500 rounded-full shadow-sm">1</span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div x-show="open" x-transition class="absolute z-50 w-48 py-2 mt-2 bg-white border border-gray-100 shadow-xl rounded-xl left-0" style="display: none;">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilih metode</div>
                <div class="flex flex-col gap-1 px-2">
                  <template x-for="opt in [{v:'semua',l:'Semua'},{v:'pos',l:'POS'},{v:'qr',l:'QR (Self Order)'}]" :key="opt.v">
                    <label class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors group">
                      <div class="relative flex items-center justify-center w-5 h-5">
                        <input type="radio" :value="opt.v" x-model="openBillFilter" @change="open = false" class="peer absolute w-5 h-5 opacity-0 cursor-pointer">
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full bg-white peer-checked:bg-emerald-500 peer-checked:border-emerald-500 flex items-center justify-center transition-colors group-hover:border-emerald-400">
                          <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        </div>
                      </div>
                      <span class="font-medium" x-text="opt.l"></span>
                    </label>
                  </template>
                </div>
              </div>
            </div>

            {{-- Dropdown Filter: Status --}}
            <div x-data="{ open: false }" class="relative shrink-0" @click.away="open = false">
              <button type="button" @click="open = !open"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 focus:outline-none transition-colors">
                <span>Status</span>
                <span x-show="openBillStatusFilter !== 'semua'" class="flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-emerald-500 rounded-full shadow-sm">1</span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div x-show="open" x-transition class="absolute z-50 w-48 py-2 mt-2 bg-white border border-gray-100 shadow-xl rounded-xl left-0" style="display: none;">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilih status</div>
                <div class="flex flex-col gap-1 px-2">
                  <template x-for="opt in [{v:'semua',l:'Semua'},{v:'aktif',l:'Aktif'},{v:'selesai',l:'Selesai'},{v:'dibatalkan',l:'Dibatalkan'}]" :key="opt.v">
                    <label class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors group">
                      <div class="relative flex items-center justify-center w-5 h-5">
                        <input type="radio" :value="opt.v" x-model="openBillStatusFilter" @change="open = false" class="peer absolute w-5 h-5 opacity-0 cursor-pointer">
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full bg-white peer-checked:bg-emerald-500 peer-checked:border-emerald-500 flex items-center justify-center transition-colors group-hover:border-emerald-400">
                          <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                        </div>
                      </div>
                      <span class="font-medium" x-text="opt.l"></span>
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

            <select x-model="riwayatStatusFilter" class="h-9 px-3 text-sm font-bold rounded-xl border border-gray-200 bg-white text-gray-700 outline-none focus:border-primary">
              <option value="semua">Semua Status</option>
              <option value="lunas">Lunas</option>
              <option value="menunggu_pembayaran">Pending</option>
              <option value="void">Void / Batal</option>
            </select>

            <select x-model="riwayatPayFilter" class="h-9 px-3 text-sm font-bold rounded-xl border border-gray-200 bg-white text-gray-700 outline-none focus:border-primary">
              <option value="semua">Semua Bayar</option>
              <option value="cash">Tunai (Cash)</option>
              <option value="qris">Nontunai (QRIS)</option>
            </select>

            <button type="button" @click="exportToCsv()"
                    title="Export CSV"
                    class="h-9 px-3.5 rounded-xl bg-primary hover:bg-primary-container text-white font-extrabold text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
              <x-heroicon-o-arrow-down-tray class="w-3 h-3" />
              <span>Export CSV</span>
            </button>
          </div>
        </div>
      </div>

      {{-- BARIS 2: Filter QR Scan Menu --}}
      <div x-show="leftView === 'qr'" class="flex items-center justify-between pt-1 pb-0.5">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
          <button type="button" class="chip-active shadow-2xs px-3.5 h-8 rounded-xl text-sm font-extrabold transition-all shrink-0">Semua Meja ({{ $mejas->count() }})</button>
        </div>
        <a href="{{ route('pos.dinein.print-qr') }}" target="_blank"
           class="h-8 px-3.5 rounded-xl bg-primary hover:bg-primary-container text-white font-extrabold text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
          <x-heroicon-o-printer class="w-3 h-3 text-emerald-400" />
          <span>Cetak Semua QR</span>
        </a>
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
    <div x-show="leftView === 'meja'" x-cloak class="flex-1 overflow-y-auto p-4 md:p-6 pb-8 bg-secondary-soft">

      <div class="bg-white border border-gray-200/80 rounded-xl shadow-xs overflow-x-auto overflow-y-visible min-h-64">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50/50 border-b border-gray-100">
              <th class="py-4 px-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider">No. Meja</th>
              <th class="py-4 px-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status Meja / KOT</th>
              <th class="py-4 px-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider text-right">Aksi Kasir</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($mejas as $meja)
            @php
              $openBillMeja = $openBills->firstWhere('meja_id', $meja->id);
              $subStatusMeja = $openBillMeja->status_pesanan_id ?? 'kosong';
            @endphp
            <tr x-show="(tableFilter === 'semua' || (tableFilter === 'kosong' && '{{ $meja->status }}' === 'kosong') || (tableFilter === 'terisi' && '{{ $meja->status }}' !== 'kosong') || (tableFilter === '{{ $subStatusMeja }}')) && (!tableSearch || '{{ strtolower(addslashes($meja->nomor_meja)) }}'.includes(tableSearch.toLowerCase()))"
                class="hover:bg-slate-50 transition-colors {{ $meja->status !== 'kosong' ? 'bg-amber-50/20' : '' }} group">
              
              {{-- Nomor Meja --}}
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-2xs {{ $meja->status === 'kosong' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }}">
                    <x-heroicon-s-squares-2x2 class="w-5 h-5 opacity-80" />
                  </div>
                  <div>
                    <span class="text-sm font-black text-slate-900 block leading-tight">{{ $meja->nomor_meja }}</span>
                    <span class="text-xs font-bold text-gray-400">Kapasitas: {{ $meja->kapasitas ?? 4 }} Orang</span>
                  </div>
                </div>
              </td>
              
              {{-- Status --}}
              <td class="py-3.5 px-4">
                <div class="space-y-1.5">
                  <span class="px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider border inline-flex items-center gap-1.5 {{ $meja->status === 'kosong' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-900 border-amber-300' }}">
                    @if($meja->status === 'kosong')
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Kosong & Tersedia
                    @else
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Sedang Terisi
                    @endif
                  </span>
                  {{-- KOT Status from polling --}}
                  <template x-if="mejaStatusMap[{{ $meja->id }}] && mejaStatusMap[{{ $meja->id }}].kot">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border inline-flex items-center gap-1"
                          :class="getKotStatusClass(mejaStatusMap[{{ $meja->id }}].kot.status_tiket_dapur_id)">
                      <x-heroicon-o-fire class="w-3 h-3" />
                      <span x-text="getKotStatusLabel(mejaStatusMap[{{ $meja->id }}].kot.status_tiket_dapur_id)"></span>
                      <span class="mono text-xs bg-white/50 px-1.5 rounded" x-text="mejaStatusMap[{{ $meja->id }}].kot.nomor_tiket"></span>
                    </span>
                  </template>
                  <template x-if="mejaStatusMap[{{ $meja->id }}] && !mejaStatusMap[{{ $meja->id }}].kot && mejaStatusMap[{{ $meja->id }}].has_active_order">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border inline-flex items-center gap-1 bg-primary-soft text-primary border-primary/20">
                      <x-heroicon-o-clock class="w-3 h-3" />
                      KOT Menunggu
                    </span>
                  </template>
                </div>
              </td>
              
              {{-- Aksi --}}
              <td class="py-3.5 px-4 text-right flex items-center justify-end gap-2 h-full">
                @if($meja->status === 'kosong')
                  <button type="button" @click="selectTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                          class="px-4 py-2 rounded-xl text-xs font-black transition-all shadow-xs flex items-center gap-1.5 min-w-[120px] justify-center bg-primary hover:bg-primary-container text-white cursor-pointer active:scale-95">
                    <span x-text="selectedTable == {{ $meja->id }} ? '✓ Terpilih' : 'Pilih Meja'"></span>
                  </button>
                @else
                  <span class="px-4 py-2 rounded-xl text-xs font-black bg-gray-50 text-gray-400 border border-gray-200 cursor-not-allowed flex items-center justify-center gap-1.5 min-w-[120px]">
                    <x-heroicon-s-lock-closed class="w-3.5 h-3.5 opacity-70" />
                    <span>Terisi</span>
                  </span>
                @endif
                
                @if($meja->status !== 'kosong')
                <form action="{{ route('pos.dinein.clear-table', $meja->id) }}" method="POST" id="form-clear-{{ $meja->id }}" class="inline-block">
                  @csrf
                  @method('PATCH')
                  <button type="button" 
                          @click="confirmClearTable({{ $meja->id }}, '{{ addslashes($meja->nomor_meja) }}')"
                          class="px-3 py-2 rounded-xl text-xs font-black text-amber-900 bg-amber-100/90 border border-amber-300 hover:bg-amber-200 transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-amber-700" />
                    <span>Kosongkan Meja</span>
                  </button>
                </form>
                @endif
              </td>
              
            </tr>
            @endforeach
          </tbody>
        </table>
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

      {{-- List Open Bills --}}
      {{-- List Open Bills Table --}}
      <div x-show="openBills.length > 0" class="pb-[150px]">
        <x-ui.table class="min-w-[1000px]">
          <x-ui.table.header>
            <th class="px-4 py-3.5 text-center w-12">No</th>
            <th class="px-4 py-3.5 text-left">Tanggal Pesan</th>
            <th class="px-4 py-3.5 text-left">No. Pesanan</th>
            <th class="px-4 py-3.5 text-left">Meja</th>
            <th class="px-4 py-3.5 text-left">Pelanggan</th>
            <th class="px-4 py-3.5 text-left">No. Telepon</th>
            <th class="px-4 py-3.5 text-left">Metode</th>
            <th class="px-4 py-3.5 text-right">Total</th>
            <th class="px-4 py-3.5 text-center">Status Pesanan</th>
            <th class="px-4 py-3.5 text-center">Status Pembayaran</th>
            <th class="px-4 py-3.5 text-right">Aksi</th>
          </x-ui.table.header>
          <tbody class="divide-y divide-gray-100">
            <template x-for="(bill, index) in openBills.filter(b => {
                if (openBillFilter === 'pos' && b.sumber_pesanan === 'self_order') return false;
                if (openBillFilter === 'qr' && b.sumber_pesanan !== 'self_order') return false;
                if (openBillStatusFilter !== 'semua' && b.status !== openBillStatusFilter) return false;
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

                {{-- No. Pesanan --}}
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
                  <span class="font-medium text-sm text-slate-900" x-text="(bill.nama_konsumen || 'Tamu').split(' – ')[0].split(' - ')[0]"></span>
                </td>

                {{-- No. Telepon --}}
                <td class="px-4 py-4 align-middle">
                  <span class="text-sm text-slate-500 font-medium"
                        x-text="bill.no_telepon || '-'"></span>
                </td>

                {{-- Metode --}}
                <td class="px-4 py-4 align-middle">
                  <span class="text-xs font-semibold px-2 py-1 rounded-lg"
                        :class="bill.sumber_pesanan === 'self_order' ? 'bg-violet-50 text-violet-700 border border-violet-200' : 'bg-primary-soft text-primary border border-primary/20'"
                        x-text="bill.sumber_pesanan === 'self_order' ? 'Self Order' : 'POS'"></span>
                </td>
                
                {{-- Total Tagihan --}}
                <td class="px-4 py-4 text-right align-middle font-bold text-gray-900 tabular-nums whitespace-nowrap">
                  <span x-text="'Rp ' + formatPrice(bill.total_tagihan || (bill.items || []).reduce((s, i) => s + (i.subtotal || ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * (i.qty || 0))), 0))"></span>
                </td>
                
                {{-- Status Pesanan --}}
                <td class="px-4 py-4 text-center align-middle">
                  <select :value="bill.status_pesanan_id || (bill.status_raw === 'selesai' ? 5 : (bill.status_raw === 'dibatalkan' ? 6 : 2))"
                          @change="updateOrderStatus(bill.id, $event.target.value)"
                          class="text-xs font-bold px-2 py-1 rounded-lg border focus:ring-primary focus:border-primary cursor-pointer transition-colors shadow-xs"
                          :class="
                            (bill.status_pesanan_id == 1) ? 'text-amber-700 bg-amber-50 border-amber-200' :
                            (bill.status_pesanan_id == 2 || !bill.status_pesanan_id) ? 'text-blue-700 bg-blue-50 border-blue-200' :
                            (bill.status_pesanan_id == 3) ? 'text-indigo-700 bg-indigo-50 border-indigo-200' :
                            (bill.status_pesanan_id == 4) ? 'text-purple-700 bg-purple-50 border-purple-200' :
                            (bill.status_pesanan_id == 5) ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-red-700 bg-red-50 border-red-200'
                          ">
                    <option value="1">Menunggu Konfirmasi</option>
                    <option value="2">Dikonfirmasi</option>
                    <option value="3">Sedang Diproses</option>
                    <option value="4">Siap Disajikan</option>
                    <option value="5">Selesai</option>
                    <option value="6">Dibatalkan</option>
                  </select>
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
                                class="flex items-center justify-center p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs">
                          <x-heroicon-o-eye class="w-4 h-4" />
                        </button>

                        <!-- Print (Dropdown): Struk Meja, Checker Dapur, Nota Pelanggan -->
                        <template x-if="bill.status !== 'Menunggu Konfirmasi'">
                          <div class="relative">
                              <button type="button" @click="activeDropdown = (activeDropdown === bill.id ? null : bill.id)" @click.away="if(activeDropdown === bill.id) activeDropdown = null"
                                      title="Print Struk"
                                      class="flex items-center justify-center p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs">
                                <x-heroicon-o-printer class="w-4 h-4" />
                              </button>
  
                              <div x-show="activeDropdown === bill.id" x-transition.opacity
                                   class="absolute top-full right-0 mt-1.5 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-[60] overflow-hidden"
                                   style="display: none;">
                                  <a href="#" @click.prevent="activeDropdown = null; openReceiptPreview(bill, 'meja')"
                                     class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-700 hover:bg-slate-50 transition-colors">
                                      <x-heroicon-o-document-text class="w-4 h-4 text-slate-400" />
                                      Struk Checker Meja
                                  </a>
                                  <a href="#" @click.prevent="activeDropdown = null; openKitchenPreview(bill)"
                                     class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-700 hover:bg-slate-50 transition-colors border-t border-gray-100">
                                      <x-heroicon-o-sparkles class="w-4 h-4 text-slate-400" />
                                      Struk Checker Dapur
                                  </a>
                                  <a href="#" @click.prevent="activeDropdown = null; openReceiptPreview(bill, 'pelanggan')"
                                     class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-700 hover:bg-slate-50 transition-colors border-t border-gray-100">
                                      <x-heroicon-o-receipt-refund class="w-4 h-4 text-slate-400" />
                                      Struk Konsumen
                                  </a>
                              </div>
                          </div>
                        </template>

                        @if(!$isPelayan)
                        <!-- Konfirmasi - khusus jika pesanan belum dikonfirmasi -->
                        <template x-if="bill.status === 'Menunggu Konfirmasi'">
                          <button type="button" @click="konfirmasiPesanan(bill)"
                                  class="px-4 py-2 bg-primary hover:bg-primary-container active:scale-[0.99] text-white rounded-xl text-xs font-black transition-all shadow-xs flex items-center gap-1.5">
                            <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                            <span>KONFIRMASI</span>
                          </button>
                        </template>

                        <!-- BAYAR -->
                        <button type="button" @click="proceedToCheckout(bill)"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl text-xs font-black transition-all shadow-xs flex items-center gap-1.5">
                          <span>BAYAR</span>
                          <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                        </button>
                        @endif
                      </div>
                    </template>
                    <template x-if="bill.status_raw !== 'aktif'">
                      <div class="flex items-center justify-end gap-1.5">
                        <a :href="'/pos/dinein/receipts/' + bill.id" target="_blank"
                           title="Struk Nota"
                           class="flex items-center justify-center p-2 bg-slate-100 hover:bg-primary-container hover:text-white text-slate-700 rounded-xl transition-all shadow-xs">
                          <x-heroicon-o-printer class="w-4 h-4" />
                        </a>
                        <button type="button" @click="openDetailModal(bill)"
                                title="Detail Transaksi"
                                class="flex items-center justify-center p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all shadow-xs">
                          <x-heroicon-o-eye class="w-4 h-4" />
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

    {{-- ══════════════════════ VIEW 5 · QR SCAN MENU (KARTU MEJA) ══════════════════════ --}}
    <div x-show="leftView === 'qr'" class="flex-1 overflow-y-auto p-4 md:p-6 pb-8 bg-secondary-soft space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-extrabold text-primary">QR Scan Menu (Kartu Meja Digital)</h2>
          <p class="text-xs text-gray-500 font-medium">Kartu QR Code Meja untuk pemesanan mandiri oleh pelanggan Saung Babakan Cinta</p>
        </div>
      </div>

      {{-- Grid QR Cards --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6 justify-items-center">
        @forelse($mejas as $m)
          @php
            $appUrl = rtrim(config('app.url'), '/');
            $path = !empty(trim($m->qr_token)) ? '/qr-menu/' . trim($m->qr_token) : '/qr-menu';
            $qrTargetUrl = $appUrl . $path;
            $logoUrl = asset('images/logo-saung.png');
            $cleanNomorMeja = trim(preg_replace('/^meja\s*/i', '', $m->nomor_meja));
          @endphp
          
          <div class="card-qr-stand w-full max-w-[300px] aspect-[1/1.55] rounded-xl overflow-hidden shadow-xl border-4 border-emerald-500/30 flex flex-col justify-between p-5 relative text-white selection:bg-transparent"
               style="background: linear-gradient(145deg, #0D3024 0%, #164032 50%, #0A2219 100%);">
              
              <!-- Dark Overlay -->
              <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/40 pointer-events-none"></div>

              <!-- Decorative Corners -->
              <div class="absolute top-3 left-3 w-4 h-4 border-t-2 border-l-2 border-amber-400/60 rounded-tl-3xl"></div>
              <div class="absolute top-3 right-3 w-4 h-4 border-t-2 border-r-2 border-amber-400/60 rounded-tr-3xl"></div>
              <div class="absolute bottom-3 left-3 w-4 h-4 border-b-2 border-l-2 border-amber-400/60 rounded-bl-3xl"></div>
              <div class="absolute bottom-3 right-3 w-4 h-4 border-b-2 border-r-2 border-amber-400/60 rounded-br-3xl"></div>

              <!-- Header -->
              <div class="relative z-10 text-center pt-1 space-y-0.5">
                  <h2 class="text-2xl font-black uppercase tracking-wider text-amber-400 drop-shadow-md leading-none">
                      SCAN MENU
                  </h2>
                  <div class="pt-2">
                      <span class="inline-flex items-center gap-1.5 px-3.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md text-white border border-amber-400/40 text-xs font-extrabold shadow-sm">
                          <x-heroicon-o-users class="w-3 h-3 text-amber-400" /> Meja {{ $cleanNomorMeja }}
                      </span>
                  </div>
              </div>

              <!-- QR Code Frame -->
              <div class="relative z-10 my-auto py-1 flex flex-col items-center">
                  <div class="bg-white rounded-xl p-3.5 shadow-2xl border-4 border-amber-400/50 relative flex items-center justify-center transform transition-transform hover:scale-[1.02]">
                      <div class="qr-card-canvas w-44 h-44 flex items-center justify-center" data-url="{{ $qrTargetUrl }}"></div>
                      <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                          <div class="w-11 h-11 rounded-full bg-white p-1 shadow-xl border-2 border-emerald-800 flex items-center justify-center overflow-hidden">
                              <img src="{{ $logoUrl }}" alt="Logo Saung" class="w-full h-full object-contain">
                          </div>
                      </div>
                  </div>
                  <div class="mt-3 text-center">
                      <p class="text-xs font-bold text-white tracking-wide">Scan QR Code untuk melihat menu</p>
                      <p class="text-xs font-medium text-amber-300 mt-0.5">Arahkan kamera HP Anda memesan langsung</p>
                  </div>
              </div>

              <!-- Footer -->
              <div class="relative z-10 text-center pb-1 pt-1.5 border-t border-amber-400/30 flex items-center justify-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-white/10 backdrop-blur-md p-1 flex items-center justify-center border border-amber-400/40 shrink-0">
                      <img src="{{ $logoUrl }}" alt="Logo Saung" class="w-full h-full object-contain">
                  </div>
                  <div class="text-left">
                      <h3 class="text-xs font-black tracking-wider text-white uppercase leading-none">SAUNG BABAKAN CINTA</h3>
                      <span class="text-xs font-semibold text-amber-300 block leading-tight mt-0.5">Rumah Makan Khas Sunda</span>
                  </div>
              </div>

          </div>
        @empty
          <div class="col-span-full py-16 text-center text-gray-400 bg-white rounded-xl border border-gray-200 w-full shadow-xs">
              <x-heroicon-o-qr-code class="w-10 h-10 mb-2 text-gray-300" />
              <p class="text-sm font-semibold text-gray-700">Belum ada data meja.</p>
          </div>
        @endforelse
      </div>
    </div>

  </div>

  {{-- ─────────────────────────────── RIGHT PANEL: CART & EMBEDDED RECEIPT ────────────────────────────── --}}
  <div x-show="leftView === 'menu'" class="w-full lg:w-[380px] xl:w-[420px] bg-white lg:border-l border-t lg:border-t-0 border-gray-200/80 flex flex-col justify-between shrink-0 shadow-xs">
    
    {{-- MODE A: CART INPUT & ITEM LIST --}}
    <template x-if="rightPanelMode === 'cart'">
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

            {{-- Input 3: No WhatsApp --}}
            <input type="text" x-model="customerPhone" placeholder="No. WhatsApp (Opsional)"
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
    </template>

    {{-- MODE B: EMBEDDED RECEIPT (STRUK DAPUR & MEJA DITAMPILKAN DI SINI) --}}
    <template x-if="rightPanelMode === 'receipt'">
      <div class="flex flex-col h-full justify-between">
        {{-- Header Status Banner --}}
        <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 via-teal-50/50 to-white flex items-center justify-between shrink-0 shadow-2xs">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-primary text-emerald-400 flex items-center justify-center text-sm font-black shadow-xs ring-4 ring-emerald-500/10">
              <x-heroicon-o-check class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-1.5">
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">PESANAN DISIMPAN</h2>
                <span class="px-2 py-0.5 rounded-full bg-primary text-emerald-300 font-black text-xs tracking-wide" x-text="savedPesananObject ? ('#' + (savedPesananObject.id_pesanan || ('DIN-' + savedPesananObject.id))) : ''"></span>
              </div>
              <p class="text-xs font-bold text-emerald-800 mt-0.5" x-text="savedPesananObject ? ((savedPesananObject.meja ? (savedPesananObject.meja.nomor_meja.startsWith('Meja') ? savedPesananObject.meja.nomor_meja : 'Meja ' + savedPesananObject.meja.nomor_meja) : 'Meja -') + ' • ' + savedPesananObject.nama_konsumen) : ''"></p>
            </div>
          </div>

          <button type="button" @click="resetCartPanel()" class="text-sm font-extrabold text-primary hover:bg-emerald-100 bg-white px-3 py-1.5 rounded-xl border border-emerald-200/90 shadow-2xs transition-all active:scale-95 cursor-pointer">
            + Pesanan Baru
          </button>
        </div>

        {{-- Receipt View Filter Tabs --}}
        <div class="px-4 py-2 bg-gray-50 border-b border-gray-200/60 flex items-center gap-1 shrink-0">
          <button type="button" @click="receiptTab = 'all'"
                  :class="receiptTab === 'all' ? 'bg-primary text-white shadow-2xs font-extrabold' : 'bg-white text-gray-600 font-bold hover:bg-gray-100 border border-gray-200/80'"
                  class="flex-1 py-1.5 text-xs rounded-xl transition-all text-center cursor-pointer">
            Semua (Gabungan)
          </button>
          <button type="button" @click="receiptTab = 'dapur'"
                  :class="receiptTab === 'dapur' ? 'bg-primary text-white shadow-2xs font-extrabold' : 'bg-white text-gray-600 font-bold hover:bg-gray-100 border border-gray-200/80'"
                  class="flex-1 py-1.5 text-xs rounded-xl transition-all text-center cursor-pointer">
            Dapur
          </button>
          <button type="button" @click="receiptTab = 'meja'"
                  :class="receiptTab === 'meja' ? 'bg-primary text-white shadow-2xs font-extrabold' : 'bg-white text-gray-600 font-bold hover:bg-gray-100 border border-gray-200/80'"
                  class="flex-1 py-1.5 text-xs rounded-xl transition-all text-center cursor-pointer">
            Meja
          </button>
        </div>

        {{-- Scrollable Receipt Cards Body --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-100/70 font-mono text-xs text-gray-800">
          
          {{-- Section 1: Checker Dapur --}}
          <template x-if="receiptTab === 'all' || receiptTab === 'dapur'">
            <div class="bg-white rounded-xl p-5 border border-gray-200/90 shadow-sm space-y-3 relative overflow-hidden">
              <div class="text-center space-y-1">
                <div class="font-black text-sm text-slate-900 tracking-wider font-sans">SAUNG BABAKAN CINTA</div>
                <div class="inline-block px-3 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-bold text-xs uppercase tracking-wider font-sans">
                  ** CHECKER DAPUR (KOT) **
                </div>
              </div>
              
              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 space-y-1 text-xs">
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">No. Order:</span><span class="font-bold text-gray-900" x-text="savedPesananObject ? ('#' + (savedPesananObject.id_pesanan || ('DIN-' + savedPesananObject.id))) : '-'"></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">Meja:</span><span class="font-extrabold text-emerald-800" x-text="savedPesananObject && savedPesananObject.meja ? (savedPesananObject.meja.nomor_meja.startsWith('Meja') ? savedPesananObject.meja.nomor_meja : 'Meja ' + savedPesananObject.meja.nomor_meja) : '-'"></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">Pelanggan:</span><span class="font-bold text-gray-900" x-text="savedPesananObject ? savedPesananObject.nama_konsumen : '-'"></span></div>
              </div>

              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              {{-- Items List --}}
              <div class="space-y-2 text-xs pt-0.5">
                <template x-for="item in (savedPesananObject ? savedPesananObject.items : [])" :key="'panel-dapur-' + item.id">
                  <div class="p-2 rounded-xl bg-slate-50/70 border border-slate-100 space-y-1">
                    <div class="flex justify-between font-bold text-gray-900">
                      <span class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 bg-slate-200 text-slate-800 rounded-xl font-extrabold text-xs" x-text="item.qty + 'x'"></span>
                        <span x-text="item.menu ? item.menu.nama : (item.nama_menu || 'Menu')"></span>
                      </span>
                      <span class="font-extrabold text-slate-800" x-text="'Rp ' + formatPrice((item.menu ? item.menu.harga : (item.harga_satuan || 0)) * item.qty)"></span>
                    </div>
                    <template x-if="item.catatan">
                      <p class="text-xs text-amber-800 font-medium italic flex items-center gap-1 pl-6">
                        <x-heroicon-o-document-text class="w-3 h-3 text-amber-600" />
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
              <div class="relative px-3 py-0.5 bg-white text-gray-500 font-bold text-xs rounded-full border border-gray-300 shadow-2xs flex items-center gap-1.5 uppercase tracking-wider font-sans">
                <x-heroicon-o-scissors class="text-gray-400 w-3 h-3" />
                <span>Potong Di Sini</span>
              </div>
            </div>
          </template>

          {{-- Section 2: Checker Meja --}}
          <template x-if="receiptTab === 'all' || receiptTab === 'meja'">
            <div class="bg-white rounded-xl p-5 border border-gray-200/90 shadow-sm space-y-3 relative overflow-hidden">
              <div class="text-center space-y-1">
                <div class="font-black text-sm text-slate-900 tracking-wider font-sans">SAUNG BABAKAN CINTA</div>
                <div class="inline-block px-3 py-0.5 rounded-full bg-primary-soft text-primary border border-primary/30 font-bold text-xs uppercase tracking-wider font-sans">
                  ** CHECKER MEJA **
                </div>
              </div>
              
              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 space-y-1 text-xs">
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">No. Order:</span><span class="font-bold text-gray-900" x-text="savedPesananObject ? ('#' + (savedPesananObject.id_pesanan || ('DIN-' + savedPesananObject.id))) : '-'"></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">Meja:</span><span class="font-extrabold text-emerald-800" x-text="savedPesananObject && savedPesananObject.meja ? (savedPesananObject.meja.nomor_meja.startsWith('Meja') ? savedPesananObject.meja.nomor_meja : 'Meja ' + savedPesananObject.meja.nomor_meja) : '-'"></span></div>
                <div class="flex justify-between items-center"><span class="text-gray-400 font-medium">Pelanggan:</span><span class="font-bold text-gray-900" x-text="savedPesananObject ? savedPesananObject.nama_konsumen : '-'"></span></div>
              </div>

              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              {{-- Items List --}}
              <div class="space-y-2 text-xs pt-0.5">
                <template x-for="item in (savedPesananObject ? savedPesananObject.items : [])" :key="'panel-meja-' + item.id">
                  <div class="p-2 rounded-xl bg-slate-50/70 border border-slate-100 space-y-1">
                    <div class="flex justify-between font-bold text-gray-900">
                      <span class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 bg-slate-200 text-slate-800 rounded-xl font-extrabold text-xs" x-text="item.qty + 'x'"></span>
                        <span x-text="item.menu ? item.menu.nama : (item.nama_menu || 'Menu')"></span>
                      </span>
                      <span class="font-extrabold text-slate-800" x-text="'Rp ' + formatPrice((item.menu ? item.menu.harga : (item.harga_satuan || 0)) * item.qty)"></span>
                    </div>
                    <template x-if="item.catatan">
                      <p class="text-xs text-amber-800 font-medium italic flex items-center gap-1 pl-6">
                        <x-heroicon-o-document-text class="w-3 h-3 text-amber-600" />
                        <span x-text="item.catatan"></span>
                      </p>
                    </template>
                  </div>
                </template>
              </div>

              <div class="border-b border-dashed border-gray-300 py-0.5"></div>

              <div class="flex justify-between items-center font-extrabold text-xs pt-0.5">
                <span class="text-gray-600">TOTAL ITEM:</span>
                <span class="px-2.5 py-1 bg-primary/10 text-primary rounded-lg text-xs font-black" x-text="(savedPesananObject && savedPesananObject.items) ? savedPesananObject.items.reduce((s, i) => s + i.qty, 0) : 0"></span>
              </div>
            </div>
          </template>
        </div>

        {{-- Bottom Action Bar --}}
        <div class="p-4 border-t border-gray-200/80 bg-white space-y-2 shrink-0 shadow-lg">
          <button type="button"
                  @click="printSilentIframe(receiptTab === 'all' ? ('/pos/dinein/pesanan/' + savedPesananId + '/print-gabungan') : (receiptTab === 'dapur' ? ('/pos/dinein/pesanan/' + savedPesananId + '/print-dapur') : ('/pos/dinein/pesanan/' + savedPesananId + '/print-meja')))"
                  class="w-full py-3.5 px-4 bg-primary hover:bg-primary-container active:scale-[0.99] text-white font-extrabold rounded-xl text-xs transition-all flex items-center justify-center gap-2 shadow-md text-center cursor-pointer">
            <x-heroicon-o-printer class="text-emerald-400 w-5 h-5" />
            <span x-text="receiptTab === 'all' ? 'Cetak Struk Dapur & Meja (1 Halaman)' : (receiptTab === 'dapur' ? 'Cetak Struk Dapur' : 'Cetak Struk Meja')"></span>
          </button>

          <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="resetCartPanel()"
                    class="py-3 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold rounded-xl text-xs transition-all text-center active:scale-95 cursor-pointer">
              + Pesanan Baru
            </button>
            <a :href="'/pos/dinein/meja/' + (savedPesananObject ? savedPesananObject.meja_id : '') + '/checkout'"
               class="py-3 px-3 bg-emerald-100 hover:bg-emerald-200 text-emerald-900 font-extrabold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 text-center active:scale-95 cursor-pointer">
              <span>BAYAR</span>
              <x-heroicon-o-chevron-right class="w-3 h-3" />
            </a>
          </div>
        </div>
      </div>
    </template>

  </div>

  </div>
  {{-- ── MODAL DETAIL TRANSAKSI LENGKAP ── --}}
  <div x-show="showTrxDetailModal" x-cloak x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-xl p-6 max-w-lg w-full space-y-4 shadow-2xl border border-gray-100" @click.outside="showTrxDetailModal = false">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <div>
          <h3 class="text-base font-extrabold text-slate-900" x-text="'Detail Transaksi #' + (selectedTrxDetail ? (selectedTrxDetail.id_pesanan || ('DIN-' + selectedTrxDetail.id)) : '')"></h3>
          <p class="text-xs text-slate-400 font-medium" x-text="selectedTrxDetail ? (selectedTrxDetail.dibuat_pada || selectedTrxDetail.created_at) : ''"></p>
        </div>
        <button type="button" @click="showTrxDetailModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
      </div>

      <template x-if="selectedTrxDetail">
        <div class="space-y-3 text-xs">
          {{-- Info Utama --}}
          <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200/80">
            <div><span class="text-slate-400 font-bold block">Pelanggan:</span> <span class="font-extrabold text-slate-900" x-text="selectedTrxDetail?.nama_konsumen || '-'"></span></div>
            <div><span class="text-slate-400 font-bold block">Meja:</span> <span class="font-extrabold text-emerald-800" x-text="selectedTrxDetail?.meja ? selectedTrxDetail.meja.nomor_meja : '-'"></span></div>
            <div><span class="text-slate-400 font-bold block">Kasir Bertugas:</span> <span class="font-extrabold text-slate-800" x-text="(selectedTrxDetail?.pembayaran?.diverifikasi_oleh_pengguna?.name) || 'Kasir'"></span></div>
            <div><span class="text-slate-400 font-bold block">Metode Bayar:</span> <span class="font-extrabold text-primary uppercase" x-text="selectedTrxDetail?.pembayaran ? selectedTrxDetail.pembayaran.metode_bayar : 'LUNAS'"></span></div>
          </div>

          {{-- Item List --}}
          <div class="space-y-1.5">
            <p class="font-extrabold text-slate-700">Rincian Menu Dipesan:</p>
            <div class="border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100 max-h-48 overflow-y-auto">
              <template x-for="item in (selectedTrxDetail.items || [])" :key="item.id">
                <div class="p-2.5 flex items-center justify-between">
                  <div>
                    <p class="font-extrabold text-slate-800" x-text="item.qty + 'x ' + (item.menu ? item.menu.nama : (item.nama_menu || 'Menu'))"></p>
                    <p class="text-xs text-slate-400" x-text="item.catatan ? ('Catatan: ' + item.catatan) : ''"></p>
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
            <div class="flex justify-between font-black text-sm text-primary pt-1 border-t border-slate-200">
              <span>Total Tagihan:</span>
              <span x-text="'Rp ' + formatPrice((selectedTrxDetail.items || []).reduce((s, i) => s + ((i.menu ? i.menu.harga : (i.harga_satuan || 0)) * i.qty), 0))"></span>
            </div>
          </div>
        </div>
      </template>

      <div class="pt-4 flex flex-wrap justify-between gap-2 border-t border-gray-100">
        <div class="flex gap-2">
            <button type="button" @click="printSilentIframe('/pos/dinein/pesanan/' + selectedTrxDetail.id + '/print-dapur')" class="px-4 py-2.5 bg-emerald-50 text-emerald-800 border border-emerald-200 font-extrabold rounded-xl text-sm hover:bg-emerald-100 transition-colors">
                <x-heroicon-o-printer class="w-5 h-5" /> Struk Dapur
            </button>
            <button type="button" @click="printSilentIframe('/pos/dinein/pesanan/' + selectedTrxDetail.id + '/print-meja')" class="px-4 py-2.5 bg-primary-soft text-primary border border-primary/20 font-extrabold rounded-xl text-sm hover:bg-primary/10 transition-colors">
                <x-heroicon-o-printer class="w-5 h-5" /> Struk Meja
            </button>
        </div>
        <button type="button" @click="showTrxDetailModal = false" class="px-5 py-2.5 bg-primary text-white font-extrabold rounded-xl text-sm hover:bg-primary/90 transition-colors cursor-pointer">
          Tutup
        </button>
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
        <button type="button" @click="printSilentIframe('/pos/dinein/pesanan/' + (checkerBill ? checkerBill.id : '') + '/print-meja')" class="px-4 py-2.5 bg-primary-soft text-primary border border-primary/20 font-extrabold rounded-xl text-sm hover:bg-primary/10 transition-colors">
          <x-heroicon-o-printer class="w-4 h-4 inline-block mr-1" /> Cetak Struk Meja
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
          <select x-model="alasanVoidInput" class="w-full h-11 px-3.5 text-sm font-extrabold rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary outline-none">
            <option value="Salah Input Menu">Salah Input Menu</option>
            <option value="Request Pembatalan Pelanggan">Request Pembatalan Pelanggan</option>
            <option value="Lainnya">Lainnya</option>
          </select>
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
                    <i class="ph ph-receipt text-xl"></i>
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
                    <i class="ph ph-cooking-pot text-xl"></i>
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

  {{-- ── MODAL PESANAN BERHASIL (SUCCESS MODAL) ── --}}
  <div x-show="showSuccessModal" x-cloak x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div class="relative z-10 w-full max-w-[480px] bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center text-center mx-4"
         @click.outside="showSuccessModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100">
        
        <button type="button" @click="showSuccessModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>

        <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-500 mb-5 relative">
            <div class="absolute inset-0 rounded-full border border-emerald-200 animate-ping opacity-50"></div>
            <x-heroicon-s-check class="w-10 h-10" />
        </div>

        <h2 class="text-2xl font-bold text-slate-800 mb-1">Pesanan Berhasil!</h2>
        <p class="text-xs text-slate-500 mb-4 font-medium uppercase tracking-wider">Nomor Pesanan</p>
        
        <div class="text-lg font-bold text-slate-800 tracking-wide mb-3" x-text="savedPesananObject ? (savedPesananObject.id_pesanan || ('DIN-' + savedPesananObject.id)) : '-'">
        </div>
        
        <div class="text-3xl font-black text-primary mb-8" x-text="savedPesananObject ? ('Rp ' + formatPrice(savedPesananObject.total_tagihan)) : 'Rp 0'">
        </div>

        <div class="w-full space-y-3">
            <button @click="window.open('/pos/dinein/pesanan/' + savedPesananId + '/print-gabungan', '_blank', 'width=400,height=700')" class="w-full py-3.5 bg-primary text-white rounded-xl font-bold text-sm hover:bg-primary-container transition flex justify-center items-center gap-2 shadow-sm">
                <x-heroicon-o-printer class="w-5 h-5 text-emerald-400" /> Cetak Struk
            </button>
            <button @click="window.open('/pos/dinein/pesanan/' + savedPesananId + '/print-dapur', '_blank', 'width=400,height=700')" class="w-full py-3.5 bg-emerald-50 border border-emerald-200 text-primary rounded-xl font-bold text-sm hover:bg-emerald-100 transition flex justify-center items-center gap-2 shadow-sm">
                <x-heroicon-o-printer class="w-5 h-5 text-primary" /> Cetak Struk Dapur
            </button>
            <button @click="showSuccessModal = false; resetCartPanel()" class="w-full py-3.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl font-bold text-sm transition flex justify-center items-center">
                Pesanan Baru
            </button>
        </div>
    </div>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate QR codes using local qrcode.js
    const qrContainers = document.querySelectorAll('.qr-card-canvas');
    qrContainers.forEach(container => {
        const url = container.getAttribute('data-url');
        if(url) {
            new QRCode(container, {
                text: url,
                width: 176,
                height: 176,
                colorDark: '#0D3024',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });
});
</script>
@endsection
