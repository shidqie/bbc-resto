<x-landing.section id="galeri" title="Galeri">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        @foreach([
            'photo-1504674900247-0877df9cc836',
            'photo-1540189549336-e6e99c3679fe',
            'photo-1565299624946-b28f40a0ae38',
            'photo-1476224203421-9ac39bcb3327',
            'photo-1512058564366-18510be2db19',
            'photo-1414235077428-338989a2e8c0',
            'photo-1555396273-367ea4eb4db5',
            'photo-1559339352-11d035aa65de'
        ] as $img)
            <div class="rounded-lg overflow-hidden aspect-square">
                <img src="https://images.unsplash.com/{{ $img }}?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Galeri" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
            </div>
        @endforeach
    </div>
</x-landing.section>
