<div id="menuModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 pointer-events-none opacity-0 transition-opacity duration-300">
    <div class="fixed inset-0 bg-neutral-900/40 backdrop-blur-sm transition-opacity" onclick="closeMenuModal()"></div>
    <div id="menuModalContent" class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-sm w-full transform scale-95 opacity-0 transition-all duration-300 relative pointer-events-auto flex flex-col max-h-[90vh]">
        <button onclick="closeMenuModal()" class="absolute top-3 right-3 bg-white/80 backdrop-blur-md hover:bg-white text-neutral-600 hover:text-neutral-900 p-1.5 rounded-full shadow-sm z-10 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="w-full h-48 sm:h-56 bg-neutral-100 shrink-0 relative" id="modalImgContainer">
            <img id="modalImg" src="" alt="" class="w-full h-full object-cover hidden">
            <div id="modalImgFallback" class="w-full h-full flex items-center justify-center text-neutral-300 hidden">
                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-xs border border-neutral-200 px-2 py-1 rounded text-[10px] font-bold text-neutral-500 uppercase tracking-wider shadow-sm" id="modalKategori"></div>
        </div>
        <div class="p-5 flex-1 overflow-y-auto">
            <h3 id="modalTitle" class="text-xl font-bold text-neutral-900 mb-2 leading-tight"></h3>
            <p id="modalHarga" class="text-primary font-bold text-lg mb-4"></p>
            <p id="modalDesc" class="text-sm text-neutral-600 leading-relaxed"></p>
        </div>
    </div>
</div>

<script>
    function openMenuModal(data) {
        document.getElementById('modalTitle').textContent = data.nama;
        document.getElementById('modalHarga').textContent = 'Rp ' + parseInt(data.harga).toLocaleString('id-ID');
        document.getElementById('modalDesc').textContent = data.deskripsi || 'Tidak ada deskripsi.';
        document.getElementById('modalKategori').textContent = data.kategori;
        
        const img = document.getElementById('modalImg');
        const fallback = document.getElementById('modalImgFallback');
        if (data.foto) {
            img.src = data.foto;
            img.classList.remove('hidden');
            fallback.classList.add('hidden');
        } else {
            img.src = '';
            img.classList.add('hidden');
            fallback.classList.remove('hidden');
        }

        const modal = document.getElementById('menuModal');
        const content = document.getElementById('menuModalContent');
        
        modal.classList.remove('pointer-events-none', 'opacity-0');
        modal.classList.add('opacity-100');
        
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
        
        document.body.style.overflow = 'hidden';
    }

    function closeMenuModal() {
        const modal = document.getElementById('menuModal');
        const content = document.getElementById('menuModalContent');
        
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            document.body.style.overflow = '';
        }, 300);
    }
</script>
