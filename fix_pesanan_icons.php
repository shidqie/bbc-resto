<?php
$file = 'resources/views/pos/pesanan/index.blade.php';
$content = file_get_contents($file);

$replacements = [
    '<i class="fa-solid fa-clock-rotate-left text-xs"></i>' => '<x-heroicon-o-clock class="w-3 h-3" />',
    '<i class="fa-solid fa-qrcode text-3xl mb-2 text-gray-300"></i>' => '<x-heroicon-o-qr-code class="w-10 h-10 mb-2 text-gray-300" />',
    '<i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="showTableDropdown ? \'rotate-180\' : \'\'"></i>' => '<x-heroicon-o-chevron-down class="w-3 h-3 text-gray-400 transition-transform" x-bind:class="showTableDropdown ? \'rotate-180\' : \'\'" />',
    '<i class="fa-solid fa-basket-shopping"></i>' => '<x-heroicon-o-shopping-bag class="w-5 h-5" />',
    '<i class="fa-solid fa-chevron-right text-[10px]"></i>' => '<x-heroicon-o-chevron-right class="w-3 h-3" />',
    '<i class="fa-solid fa-xmark text-sm"></i>' => '<x-heroicon-o-x-mark class="w-4 h-4" />',
    '<i class="fa-solid fa-print"></i>' => '<x-heroicon-o-printer class="w-5 h-5" />'
];

foreach ($replacements as $old => $new) {
    $content = str_replace($old, $new, $content);
}

file_put_contents($file, $content);
echo "Replaced icons in pesanan index.\n";
?>
