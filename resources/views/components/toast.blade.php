{{--
|--------------------------------------------------------------------------
| Toast Component
|--------------------------------------------------------------------------
| Notifikasi toast modern yang tampil di kanan atas lalu menghilang otomatis.
|
| Cara pakai:
|   1) Session flash (server side):
|        return redirect()->back()->with('success', 'Data disimpan.');
|      Komponen ini otomatis membaca session 'success', 'error', 'warning',
|      dan 'info' lalu menampilkannya sebagai toast.
|
|   2) Dari JavaScript (client side):
|        window.showToast('success', 'Data berhasil disimpan.');
|        window.showToast('error', 'Terjadi kesalahan.');
|        window.showToast('warning', 'Perhatian!');
|        window.showToast('info', 'Informasi.');
|
| Saluran toast ("type") yang didukung:
|   success (hijau) | error (merah) | warning (amber) | info (biru)
|--------------------------------------------------------------------------------
--}}

@once
<script>
(function () {
    if (window.showToast) return;

    var container = null;

    function initContainer() {
        if (container) return;
        container = document.createElement('div');
        container.className = 'app-toast-container';
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);

        if (!document.getElementById('app-toast-styles')) {
            var style = document.createElement('style');
            style.id = 'app-toast-styles';
            style.textContent = [
                '.app-toast-container{position:fixed;top:1rem;right:1rem;z-index:99999;display:flex;flex-direction:column;gap:.625rem;width:min(100% - 2rem,22rem);pointer-events:none}',
                '.app-toast{pointer-events:auto;display:flex;align-items:flex-start;gap:.625rem;background:#fff;border:1px solid #e5e7eb;border-left-width:4px;border-radius:.625rem;padding:.75rem .875rem;box-shadow:0 10px 30px -12px rgba(0,0,0,.25);font-size:.8125rem;color:#111827;animation:app-toast-in .25s ease-out}',
                '.app-toast.app-toast-out{animation:app-toast-out .25s ease-in forwards}',
                '@keyframes app-toast-in{from{opacity:0;transform:translateX(1rem)}to{opacity:1;transform:translateX(0)}}',
                '@keyframes app-toast-out{to{opacity:0;transform:translateX(1rem)}}',
                '.app-toast-icon{flex-shrink:0;margin-top:.125rem}',
                '.app-toast-msg{flex:1;min-width:0;font-weight:500;line-height:1.35;word-break:break-word}',
                '.app-toast-close{flex-shrink:0;color:#9ca3af;background:none;border:none;cursor:pointer;padding:.125rem;border-radius:.375rem;line-height:0;transition:color .15s,background-color .15s}',
                '.app-toast-close:hover{color:#4b5563;background:#f3f4f6}',
                '.app-toast-success{border-left-color:#10b981}.app-toast-success .app-toast-icon{color:#10b981}',
                '.app-toast-error{border-left-color:#ef4444}.app-toast-error .app-toast-icon{color:#ef4444}',
                '.app-toast-warning{border-left-color:#f59e0b}.app-toast-warning .app-toast-icon{color:#f59e0b}',
                '.app-toast-info{border-left-color:#3b82f6}.app-toast-info .app-toast-icon{color:#3b82f6}',
                '@media(max-width:640px){.app-toast-container{width:calc(100% - 1.5rem);right:.75rem;top:.75rem}}'
            ].join('');
            document.head.appendChild(style);
        }
    }

    var icons = {
        success: '<svg xmlns="http://www.w3.org/2000/svg" class="app-toast-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        error: '<svg xmlns="http://www.w3.org/2000/svg" class="app-toast-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        warning: '<svg xmlns="http://www.w3.org/2000/svg" class="app-toast-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        info: '<svg xmlns="http://www.w3.org/2000/svg" class="app-toast-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };

    window.showToast = function (type, message) {
        initContainer();
        type = (['success', 'error', 'warning', 'info'].indexOf(type) !== -1) ? type : 'info';

        var toast = document.createElement('div');
        toast.className = 'app-toast app-toast-' + type;
        toast.innerHTML =
            (icons[type] || icons.info) +
            '<span class="app-toast-msg"></span>' +
            '<button type="button" class="app-toast-close" aria-label="Tutup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>';
        toast.querySelector('.app-toast-msg').textContent = message;

        var closing = false;
        var remove = function () {
            if (closing) return;
            closing = true;
            toast.classList.add('app-toast-out');
            setTimeout(function () { toast.remove(); }, 260);
        };

        var timer = setTimeout(remove, 4500);
        toast.querySelector('.app-toast-close').addEventListener('click', remove);
        toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
        toast.addEventListener('mouseleave', function () { timer = setTimeout(remove, 2000); });

        container.appendChild(toast);
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    @if (session('success'))
        window.showToast('success', @js(session('success')));
    @endif
    @if (session('error'))
        window.showToast('error', @js(session('error')));
    @endif
    @if (session('warning'))
        window.showToast('warning', @js(session('warning')));
    @endif
    @if (session('info'))
        window.showToast('info', @js(session('info')));
    @endif
});
</script>
@endonce