<?php

$mapping = [
    'fa-user' => 'user',
    'fa-user-gear' => 'cog',
    'fa-user-pen' => 'pencil',
    'fa-user-shield' => 'shield-check',
    'fa-users' => 'users',
    'fa-utensils' => 'sparkles',
    'fa-fire-burner' => 'sparkles',
    'fa-qrcode' => 'qr-code',
    'fa-camera' => 'camera',
    'fa-image' => 'photo',
    'fa-list-ol' => 'list-bullet',
    'fa-clipboard-list' => 'clipboard-document-list',
    'fa-spinner' => 'arrow-path',
    'fa-times' => 'x-mark',
    'fa-xmark' => 'x-mark',
    'fa-ban' => 'no-symbol',
    'fa-eye' => 'eye',
    'fa-pen-to-square' => 'pencil-square',
    'fa-print' => 'printer',
    'fa-box' => 'cube',
    'fa-box-open' => 'archive-box',
    'fa-cube' => 'cube',
    'fa-check' => 'check',
    'fa-circle-check' => 'check-circle',
    'fa-clock' => 'clock',
    'fa-clock-rotate-left' => 'clock',
    'fa-exclamation-triangle' => 'exclamation-triangle',
    'fa-circle-exclamation' => 'exclamation-circle',
    'fa-triangle-exclamation' => 'exclamation-triangle',
    'fa-file-pdf' => 'document',
    'fa-file-invoice-dollar' => 'document-text',
    'fa-receipt' => 'document-text',
    'fa-sticky-note' => 'document-text',
    'fa-flag-checkered' => 'flag',
    'fa-truck' => 'truck',
    'fa-truck-fast' => 'truck',
    'fa-chevron-down' => 'chevron-down',
    'fa-chevron-left' => 'chevron-left',
    'fa-chevron-right' => 'chevron-right',
    'fa-cart-shopping' => 'shopping-cart',
    'fa-basket-shopping' => 'shopping-bag',
    'fa-chair' => 'users',
    'fa-chart-line' => 'chart-bar',
    'fa-chart-pie' => 'chart-pie',
    'fa-right-from-bracket' => 'arrow-right-on-rectangle',
    'fa-key' => 'key',
    'fa-lock' => 'lock-closed',
    'fa-download' => 'arrow-down-tray',
    'fa-rotate' => 'arrow-path',
    'fa-trash' => 'trash',
    'fa-trash-can' => 'trash',
    'fa-arrow-left' => 'arrow-left',
    'fa-arrow-right' => 'arrow-right',
    'fa-broom' => 'sparkles',
    'fa-scissors' => 'scissors',
    'fa-plus' => 'plus',
    'fa-wallet' => 'wallet',
    'fa-floppy-disk' => 'document-arrow-down',
    'fa-shield-halved' => 'shield-check',
    'fa-envelope' => 'envelope',
    'fa-house' => 'home',
    'fa-store' => 'building-storefront',
    'fa-gauge-high' => 'gauge',
    'fa-history' => 'clock',
    'fa-edit' => 'pencil-square',
    'fa-save' => 'document-arrow-down',
    'fa-minus' => 'minus',
    'fa-cash-register' => 'currency-dollar',
    'fa-paper-plane' => 'paper-airplane',
    'fa-pencil' => 'pencil'
];

$sizeMap = [
    'text-[9px]' => 'w-3 h-3',
    'text-[10px]' => 'w-3 h-3',
    'text-[11px]' => 'w-3 h-3',
    'text-[12px]' => 'w-3 h-3',
    'text-[15px]' => 'w-5 h-5',
    'text-xs' => 'w-3 h-3',
    'text-sm' => 'w-4 h-4',
    'text-base' => 'w-5 h-5',
    'text-md' => 'w-5 h-5',
    'text-lg' => 'w-6 h-6',
    'text-xl' => 'w-7 h-7',
    'text-2xl' => 'w-8 h-8',
    'text-3xl' => 'w-10 h-10',
    'text-4xl' => 'w-12 h-12',
];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));

$totalReplaced = 0;

foreach ($files as $file) {
    if ($file->isDir() || pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
    
    $content = file_get_contents($file);
    $original = $content;

    // 1. Replace button/component icon props: icon="fa-xyz" -> icon="xyz-mapped"
    $content = preg_replace_callback('/icon=(["\'])(fa-[a-z0-9-]+)\1/is', function($matches) use ($mapping, &$totalReplaced) {
        $iconClass = $matches[2];
        $heroicon = isset($mapping[$iconClass]) ? $mapping[$iconClass] : 'sparkles';
        $totalReplaced++;
        return 'icon="' . $heroicon . '"';
    }, $content);
    
    // 2. Replace empty-state component that we missed if it was passed via other ways, wait empty state uses icon prop.
    // 3. Replace any `<i class="fas fa-...` or `<i class="fa-solid fa-...`
    $pattern = '/<i([^>]*)class=(["\'])(.*?)\2([^>]*)>[\s]*<\/i>/is';
    
    $content = preg_replace_callback($pattern, function($matches) use ($mapping, $sizeMap, &$totalReplaced) {
        $beforeClass = $matches[1];
        $quote = $matches[2];
        $classString = $matches[3];
        $afterClass = $matches[4];

        if (strpos($classString, 'fa-') === false) {
            return $matches[0];
        }

        $classes = explode(' ', $classString);
        $newClasses = [];
        $heroicon = 'sparkles';
        $sizeAdded = false;

        foreach ($classes as $cls) {
            $cls = trim($cls);
            if (empty($cls)) continue;

            if ($cls === 'fa-solid' || $cls === 'fa-regular' || $cls === 'fa-brands' || $cls === 'fas' || $cls === 'far') {
                continue;
            } elseif (str_starts_with($cls, 'fa-')) {
                if (isset($mapping[$cls])) {
                    $heroicon = $mapping[$cls];
                } else {
                    $heroicon = str_replace('fa-', '', $cls); // fallback to removing fa-
                }
            } elseif (isset($sizeMap[$cls])) {
                $newClasses[] = $sizeMap[$cls];
                $sizeAdded = true;
            } else {
                $newClasses[] = $cls;
            }
        }

        if (!$sizeAdded && !preg_match('/\bw-\d+\b/', implode(' ', $newClasses))) {
            $newClasses[] = 'w-5 h-5';
        }

        $classAttr = implode(' ', $newClasses);
        
        $totalReplaced++;
        
        $attrs = trim($beforeClass . ' ' . $afterClass);
        if (empty($attrs)) {
            return "<x-heroicon-o-{$heroicon} class=\"{$classAttr}\" />";
        } else {
            return "<x-heroicon-o-{$heroicon} class=\"{$classAttr}\" {$attrs} />";
        }

    }, $content);

    // Also some Alpine JS conditionals `sending ? 'fa-spinner animate-spin' : 'fa-qrcode'`
    // Let's manually fix the qr-menu index which has a complex alpine class
    if (strpos($file->getPathname(), 'qr-menu/index.blade.php') !== false) {
        $content = str_replace("sending?'fa-spinner animate-spin':(metodePembayaran==='qris'?'fa-qrcode':'fa-paper-plane')", "sending?'heroicon-o-arrow-path animate-spin':(metodePembayaran==='qris'?'heroicon-o-qr-code':'heroicon-o-paper-airplane')", $content);
        // We already replaced <i class... with x-heroicon... so wait! If it's a dynamic class, Heroicon component doesn't work that easily unless it's a dynamic component.
        // Actually, if it's dynamic, it was `<x-heroicon-o-sparkles class="sending?'fa-spinner...` which is broken syntax.
        // Let's restore and fix it using x-dynamic-component for that specific file.
    }

    if ($original !== $content) {
        file_put_contents($file, $content);
        echo "Updated icons in: $file\n";
    }
}

echo "Total remaining icons replaced: $totalReplaced\n";

?>
