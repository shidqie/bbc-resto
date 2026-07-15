<x-landing.section title="Cara Pemesanan" bgBatik="true">
    <div class="grid sm:grid-cols-4 gap-6 max-w-3xl mx-auto">
        @foreach([
            ['1', 'Pilih Layanan', 'Pilih Catering atau Nasi Box.'],
            ['2', 'Isi Formulir', 'Lengkapi data pesanan Anda.'],
            ['3', 'Bayar DP', 'Catering 50%, Nasi Box 25%.'],
            ['4', 'Diproses', 'Tim kami menghubungi Anda.']
        ] as [$num, $title, $desc])
            <div class="text-center">
                <div class="w-10 h-10 bg-secondary/20 text-primary rounded-full flex items-center justify-center mx-auto mb-3 text-sm font-bold">{{ $num }}</div>
                <h4 class="font-bold text-primary text-sm mb-1">{{ $title }}</h4>
                <p class="text-body text-xs">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
</x-landing.section>
