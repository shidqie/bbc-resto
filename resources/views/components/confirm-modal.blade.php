{{--
|--------------------------------------------------------------------------
| Confirm Modal Component
|--------------------------------------------------------------------------
| Modal konfirmasi yang dapat digunakan ulang untuk aksi menghapus data.
|
| Cara pakai:
|   1) Pastikan komponen dirender sekali pada halaman:
|        <x-confirm-modal />
|
|   2) Panggil dari tombol hapus dengan window.confirmDialog(...):
|        window.confirmDialog({
|            title: 'Hapus Bahan Baku',
|            name: 'Tepung Terigu',                     // Nama data yang dihapus
|            message: 'Data yang dihapus tidak dapat dikembalikan.',
|            form: document.getElementById('form-hapus-1'),  // Form (berisi @csrf @method('DELETE'))
|            // atau gunakan formId + formAction:
|            formId: 'form-hapus-1',
|            formAction: '/bahan-baku/1',
|            confirmText: 'Hapus',
|            cancelText: 'Batal',
|            type: 'danger',                             // 'danger' | 'warning'
|            onConfirm: function () { ... }              // opsional, tanpa form
|        });
|
| Tampilan: berisi judul konfirmasi, nama data, info tidak dapat dikembalikan,
| tombol Batal, dan tombol Hapus.
|--------------------------------------------------------------------------------
--}}

@props([
    'title' => 'Konfirmasi Hapus',
])

<div
    x-data="{
        open: false,
        mtitle: 'Konfirmasi Hapus',
        mname: '',
        mmessage: 'Anda yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.',
        mconfirm: 'Hapus',
        mcancel: 'Batal',
        iconType: 'danger',
        mform: null,
        mformId: null,
        mformAction: null,
        monConfirm: null,
        promptEnabled: false,
        promptPlaceholder: '',
        mpromptValue: '',

        confirmAction() {
            const self = this;

            const resolveForm = function () {
                if (self.mform && self.mform.tagName === 'FORM') return self.mform;
                if (self.mformId) {
                    const f = document.getElementById(self.mformId);
                    if (f) return f;
                }
                if (self.mformAction) {
                    const f = document.createElement('form');
                    f.method = 'POST';
                    f.action = self.mformAction;
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.querySelector('meta[name=&quot;csrf-token&quot;]')?.content || '';
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    f.appendChild(csrf);
                    f.appendChild(method);
                    document.body.appendChild(f);
                    return f;
                }
                return null;
            };

            self.open = false;

            if (typeof self.monConfirm === 'function') {
                self.monConfirm();
                return;
            }

            const form = resolveForm();
            if (!form) return;

            // Dukungan input alasan pembatalan (pengganti prompt native)
            if (self.promptEnabled) {
                const field = form.querySelector('[name=' + (self.promptFieldName || 'alasan_batal') + ']');
                const val = (self.mpromptValue || '').trim();
                if (!val) {
                    self.open = true;
                    if (self.$refs.promptInput) setTimeout(() => self.$refs.promptInput.focus(), 80);
                    return;
                }
                if (field) field.value = val;
            }

            form.submit();
        },
    }"
    x-init="$nextTick(() => {
        const self = this;
        window.confirmDialog = function (options) {
            options = options || {};
            self.mtitle      = options.title || 'Konfirmasi Hapus';
            self.mname       = options.name || '';
            self.mmessage    = options.message || 'Anda yakin ingin menghapus data ini? Data yang dihapus tidak dapat dikembalikan.';
            self.mconfirm    = options.confirmText || 'Hapus';
            self.mcancel     = options.cancelText || 'Batal';
            self.iconType    = options.type || 'danger';
            self.mform       = options.form || null;
            self.mformId     = options.formId || null;
            self.mformAction = options.formAction || null;
            self.monConfirm  = options.onConfirm || null;
            self.promptEnabled = options.prompt || false;
            self.promptFieldName = options.promptFieldName || 'alasan_batal';
            self.promptPlaceholder = options.promptPlaceholder || 'Tulis alasan…';
            self.mpromptValue = '';
            self.open = true;
            if (self.$refs.confirmBtn) setTimeout(() => self.$refs.confirmBtn.focus(), 80);
        };
        window.confirmPrompt = function (options) {
            options = options || {};
            options.prompt = true;
            window.confirmDialog(options);
        };
        window.closeConfirmDialog = function () {
            self.open = false;
        };
    })"
>
    {{ $slot ?? '' }}

    <div x-show="open" x-cloak class="fixed inset-0 z-[120] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" x-show="open" x-transition.opacity x-on:click="open = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto border border-gray-100"
             x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                         :class="iconType == 'warning' ? 'bg-amber-50' : 'bg-red-50'">
                        <svg x-show="iconType == 'warning'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg x-show="iconType != 'warning'" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-bold text-gray-900" x-text="mtitle"></h3>
                        <p class="mt-1 text-sm text-gray-500 font-medium" x-text="mname"></p>
                        <p class="mt-2 text-xs text-gray-400" x-text="mmessage"></p>
                        <input x-show="promptEnabled" type="text" x-model="mpromptValue"
                               :placeholder="promptPlaceholder"
                               x-ref="promptInput"
                               class="mt-3 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 shadow-sm outline-none transition-all focus:border-gray-400 focus:ring-1 focus:ring-gray-400">
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" x-on:click="open = false"
                        class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                        x-text="mcancel"></button>
                <button type="button" x-ref="confirmBtn" x-on:click="confirmAction()"
                        class="px-5 py-2 text-sm font-semibold text-white rounded-xl transition-colors"
                        :class="iconType == 'warning' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-red-500 hover:bg-red-600'"
                        x-text="mconfirm"></button>
            </div>
        </div>
    </div>
</div>