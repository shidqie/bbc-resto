<?php

$filesToFix = [
    'resources/views/order/catering/show.blade.php' => [
        '<i class="fa-solid fa-clock mr-2"></i>' => '<x-heroicon-o-clock class="w-4 h-4 mr-2" />',
    ],
    'resources/views/order/nasi-box/show.blade.php' => [
        '<i class="fa-solid fa-clock mr-2"></i>' => '<x-heroicon-o-clock class="w-4 h-4 mr-2" />',
    ],
    'resources/views/auth/admin-login.blade.php' => [
        '<i class="fa-solid fa-shield-halved text-white/60 text-[10px]"></i>' => '<x-heroicon-o-shield-check class="w-3 h-3 text-white/60" />',
        '<i class="fa-solid fa-eye text-sm" x-show="!show"></i>' => '<x-heroicon-o-eye class="w-4 h-4" x-show="!show" />',
    ],
    'resources/views/laporan/pengadaan/index.blade.php' => [
        '<i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300 block"></i>' => '<x-heroicon-o-archive-box class="w-12 h-12 mb-3 text-gray-300 block" />',
    ],
    'resources/views/laporan/stok/index.blade.php' => [
        '<i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300"></i>' => '<x-heroicon-o-archive-box class="w-12 h-12 mb-3 text-gray-300" />',
    ],
    'resources/views/laporan/penjualan/index.blade.php' => [
        '<i class="fa-solid fa-receipt text-4xl mb-3 text-gray-300"></i>' => '<x-heroicon-o-document-text class="w-12 h-12 mb-3 text-gray-300" />',
    ],
    'resources/views/laporan/menu/index.blade.php' => [
        '<i class="fa-solid fa-chart-bar text-4xl mb-3 text-gray-300 block"></i>' => '<x-heroicon-o-chart-bar class="w-12 h-12 mb-3 text-gray-300 block" />',
    ],
    'resources/views/admin/pesanan/index.blade.php' => [
        '<i class="fa-solid fa-filter mr-1.5"></i>' => '<x-heroicon-o-funnel class="w-4 h-4 mr-1.5" />',
    ],
    'resources/views/components/ui/stat-card.blade.php' => [
        "'icon'  => 'fa-bag-shopping'," => "'icon'  => 'shopping-bag',",
    ],
    'resources/views/components/ui/empty-state.blade.php' => [
        "'icon'    => 'fa-box-open'," => "'icon'    => 'archive-box',",
    ],
    'resources/views/pos/meja/index.blade.php' => [
        '<i class="fa-solid fa-file-pdf"></i>' => '<x-heroicon-o-document class="w-4 h-4" />',
        '<i class="fa-solid fa-triangle-exclamation"></i>' => '<x-heroicon-o-exclamation-triangle class="w-4 h-4" />',
        '<i class="fa-solid fa-rotate"></i>' => '<x-heroicon-o-arrow-path class="w-4 h-4" />',
    ],
    'resources/views/profile/partials/update-password-form.blade.php' => [
        '<i class="fa-solid fa-key text-gray-400 text-[11px]"></i>' => '<x-heroicon-o-key class="w-3 h-3 text-gray-400" />',
        '<i class="fa-solid fa-shield-halved text-gray-400 text-[11px]"></i>' => '<x-heroicon-o-shield-check class="w-3 h-3 text-gray-400" />',
        '<i class="fa-solid fa-floppy-disk text-xs"></i>' => '<x-heroicon-o-document-arrow-down class="w-3 h-3" />',
    ],
    'resources/views/profile/partials/update-profile-information-form.blade.php' => [
        '<i class="fa-solid fa-envelope text-gray-400 text-[11px]"></i>' => '<x-heroicon-o-envelope class="w-3 h-3 text-gray-400" />',
        '<i class="fa-solid fa-check text-xs"></i>' => '<x-heroicon-o-check class="w-3 h-3" />',
    ],
    'resources/views/inventory/pengadaan/index.blade.php' => [
        '<i class="fa-solid fa-check text-xs"></i>' => '<x-heroicon-o-check class="w-3 h-3" />',
    ],
    'resources/views/inventory/pengadaan/terima.blade.php' => [
        '<i class="fa-solid fa-check"></i>' => '<x-heroicon-o-check class="w-4 h-4" />',
    ],
    'resources/views/inventory/pengadaan/terima-barang.blade.php' => [
        '<i class="fa-solid fa-eye text-xs"></i>' => '<x-heroicon-o-eye class="w-3 h-3" />',
    ],
    'resources/views/inventory/bahan-baku/index.blade.php' => [
        '<i class="fa-solid fa-eye text-xs"></i>' => '<x-heroicon-o-eye class="w-3 h-3" />',
    ],
    'resources/views/inventory/mutasi-stok/index.blade.php' => [
        '<i class="fa-solid fa-eye text-xs"></i>' => '<x-heroicon-o-eye class="w-3 h-3" />',
    ],
    'resources/views/inventory/stok-operasional/index.blade.php' => [
        '<i class="fa-solid fa-eye text-xs"></i>' => '<x-heroicon-o-eye class="w-3 h-3" />',
    ],
    'resources/views/inventory/stok-catering/index.blade.php' => [
        '<i class="fa-solid fa-utensils text-violet-600 text-sm"></i>' => '<x-heroicon-o-sparkles class="w-4 h-4 text-violet-600" />',
        '<i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ \'rotate-180\': open }"></i>' => '<x-heroicon-o-chevron-down class="w-3 h-3 text-gray-400 transition-transform duration-200" x-bind:class="{ \'rotate-180\': open }" />',
    ],
    'resources/views/menu/qr-menu/index.blade.php' => [
        '<i class="fas fa-times"></i>' => '<x-heroicon-o-x-mark class="w-4 h-4" />',
        '<i class="fas fa-plus text-xs"></i>' => '<x-heroicon-o-plus class="w-3 h-3" />',
        '<i class="fas fa-arrow-right text-xs"></i>' => '<x-heroicon-o-arrow-right class="w-3 h-3" />',
        '<i class="fas fa-minus"></i>' => '<x-heroicon-o-minus class="w-4 h-4" />',
        '<i class="fas fa-cash-register"></i>' => '<x-heroicon-o-currency-dollar class="w-4 h-4" />',
        '<i class="fas fa-qrcode"></i>' => '<x-heroicon-o-qr-code class="w-4 h-4" />',
        '<i class="fas fa-spinner animate-spin text-2xl"></i>' => '<x-heroicon-o-arrow-path class="w-8 h-8 animate-spin" />',
    ],
    'resources/views/menu/qr-menu/scanner.blade.php' => [
        '<i class="fas fa-list-ol"></i>' => '<x-heroicon-o-list-bullet class="w-4 h-4" />',
    ],
    'resources/views/menu/menu/index.blade.php' => [
        '<i class="fa-solid fa-eye text-xs"></i>' => '<x-heroicon-o-eye class="w-3 h-3" />',
    ],
    'resources/views/menu/kategori/index.blade.php' => [
        '<i class="fa-solid fa-pen-to-square text-xs"></i>' => '<x-heroicon-o-pencil-square class="w-3 h-3" />',
    ],
];

$count = 0;
foreach ($filesToFix as $file => $replacements) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $original = $content;
        foreach ($replacements as $old => $new) {
            $content = str_replace($old, $new, $content);
        }
        if ($original !== $content) {
            file_put_contents($file, $content);
            $count++;
            echo "Fixed $file\n";
        }
    }
}
echo "Total files updated: $count\n";
