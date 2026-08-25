{{-- Halaman: Notifikasi Stok --}}
@extends('layouts.pos')
@section('title', 'Notifikasi Stok')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Notifikasi Stok" subtitle="Peringatan stok menipis, habis, dan mutasi stok." :breadcrumbs="['Persediaan', 'Notifikasi Stok']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('pengadaan.harian.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-primary rounded-lg px-3 py-2 hover:bg-primary/90 transition-colors">
                        <x-heroicon-o-shopping-cart class="w-3 h-3" />
                        Buat Pengadaan
                    </a>
                    <button type="button" id="btnCheckNow" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Cek Sekarang
                    </button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Total</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-primary/20 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Belum Dibaca</p>
                <p class="text-xl font-bold text-primary mt-1">{{ $stats['unread'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-amber-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Menipis</p>
                <p class="text-xl font-bold text-amber-600 mt-1">{{ $stats['menipis'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Habis</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['habis'] }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between shrink-0">
            <form method="GET" action="{{ route('notifikasi-stok.index') }}" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <x-ui.multi-select name="jenis" :options="['menipis' => 'Menipis', 'habis' => 'Habis', 'penerimaan' => 'Penerimaan', 'penyesuaian' => 'Penyesuaian']" :selected="request('jenis', [])" label="Semua Jenis" type="radio" />
                <x-ui.multi-select name="dibaca" :options="['false' => 'Belum Dibaca', 'true' => 'Sudah Dibaca']" :selected="request('dibaca', [])" label="Semua Status" type="radio" />
                @if(request()->hasAny(['jenis', 'dibaca']))
                    <a href="{{ route('notifikasi-stok.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
            <button type="button" id="btnMarkAll" class="text-sm font-medium text-primary bg-primary-soft hover:bg-primary/10 rounded-lg px-3 py-2 transition-colors shrink-0">
                Tandai Semua Dibaca
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left">Pesan</th>
                    <th class="px-4 py-3.5 text-left">Bahan Baku</th>
                    <th class="px-4 py-3.5 text-right">Stok / Min</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($notifications as $n)
                    <tr class="{{ $n->dibaca ? 'opacity-60' : 'bg-amber-50/30' }} transition-colors">
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-800 {{ $n->dibaca ? '' : 'font-semibold' }}">{{ $n->pesan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($n->dibuat_pada)->translatedFormat('d M Y H:i') }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $n->bahan_baku?->nama_bahan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($n->stok_saat_ini, 3, ',', '.') }} / {{ number_format($n->stok_minimal, 3, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusMeta = match ($n->jenis) {
                                    'habis' => ['Habis', 'danger'],
                                    'menipis' => ['Menipis', 'warning'],
                                    'penerimaan' => ['Penerimaan', 'success'],
                                    'penyesuaian' => ['Penyesuaian', 'primary'],
                                    default => [ucfirst($n->jenis), 'gray'],
                                };
                            @endphp
                            <x-ui.badge :color="$statusMeta[1]" size="sm">{{ $statusMeta[0] }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(!$n->dibaca)
                            <button onclick="markRead({{ $n->id }}, this)" class="text-xs font-semibold text-primary bg-primary-soft hover:bg-primary/10 rounded-lg px-2.5 py-1.5 transition-colors">Tandai Dibaca</button>
                            @else
                            <span class="text-xs text-gray-400">Dibaca</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="exclamation" title="Tidak ada notifikasi stok" :colspan="5" />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notifications->hasPages())
        <div class="mt-4">{{ $notifications->links() }}</div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
const BASE_URL = '{{ url('/') }}';

function markRead(id, btn) {
    fetch(`${BASE_URL}/notifikasi-stok/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json())
        .then(() => {
            const row = btn.closest('tr');
            row.classList.add('opacity-60');
            row.classList.remove('bg-amber-50/30');
            btn.closest('td').innerHTML = '<span class="text-xs text-gray-400">Dibaca</span>';
            setTimeout(() => window.location.reload(), 300);
        });
}

document.getElementById('btnMarkAll')?.addEventListener('click', () => {
    fetch(`${BASE_URL}/notifikasi-stok/mark-all-read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json())
        .then(() => window.location.reload());
});

document.getElementById('btnCheckNow')?.addEventListener('click', () => {
    fetch(`${BASE_URL}/notifikasi-stok/check-now`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json())
        .then(d => {
            window.showToast('success', d.message);
            window.location.reload();
        });
});
</script>
@endpush
