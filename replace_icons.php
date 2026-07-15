<?php
$mapping = [
    'filter' => 'funnel',
    'file-pdf' => 'document-text',
    'box-open' => 'archive-box',
    'money-bill-wave' => 'banknotes',
    'receipt' => 'receipt-percent',
    'inbox' => 'inbox',
    'plus' => 'plus',
    'check-circle' => 'check-circle',
    'cube' => 'cube',
    'eye' => 'eye',
    'pencil' => 'pencil-square',
    'trash' => 'trash',
    'arrow-left' => 'arrow-left',
    'save' => 'document-check',
    'check' => 'check',
    'info-circle' => 'information-circle',
    'exclamation-circle' => 'exclamation-circle',
    'whatsapp' => 'chat-bubble-left-ellipsis',
    'user' => 'user',
    'calendar' => 'calendar',
    'paper-plane' => 'paper-airplane',
    'flag-checkered' => 'flag',
    'times' => 'x-mark',
    'search' => 'magnifying-glass',
    'trash-alt' => 'trash',
    'ellipsis-v' => 'ellipsis-vertical',
    'edit' => 'pencil-square',
    'ban' => 'no-symbol',
    'times-circle' => 'x-circle',
    'exclamation-triangle' => 'exclamation-triangle',
    'truck' => 'truck',
    'map-marker-alt' => 'map-pin',
    'calendar-times' => 'calendar-days',
    'angle-right' => 'chevron-right',
    'bell' => 'bell',
    'image' => 'photo',
    'store' => 'building-storefront',
    'concierge-bell' => 'bell-alert',
    'box' => 'cube',
    'list' => 'list-bullet',
    'camera' => 'camera',
    'utensils' => 'cake',
    'shopping-cart' => 'shopping-cart',
    'chevron-up' => 'chevron-up',
    'print' => 'printer',
    'tasks' => 'clipboard-document-list'
];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
$count = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        $replaced = preg_replace_callback('/<i\s+class="([^"]*?(?:fa[sbr]|fa-(?:solid|brands|regular))\s+fa-[a-zA-Z0-9\-]+[^"]*?)"[^>]*><\/i>/i', function($matches) use ($mapping) {
            $classes = $matches[1];
            
            // Extract the specific FA icon name
            if (preg_match('/fa-(?!solid|brands|regular)([a-zA-Z0-9\-]+)/', $classes, $iconMatch)) {
                $faIcon = $iconMatch[1];
            } else {
                return $matches[0];
            }
            
            // Remove the fa-* classes to leave only utility classes
            $utilityClasses = preg_replace('/(?:fa[sbr]|fa-(?:solid|brands|regular))\s+fa-[a-zA-Z0-9\-]+/', '', $classes);
            $utilityClasses = trim(preg_replace('/\s+/', ' ', $utilityClasses));
            
            // Determine replacement icon
            $heroIcon = $mapping[$faIcon] ?? 'star';
            
            $sizeClasses = preg_match('/text-(?:xl|2xl|3xl|4xl|5xl|6xl)/', $utilityClasses) ? 'w-[1em] h-[1em]' : 'w-5 h-5';
            
            $finalClasses = trim("$utilityClasses $sizeClasses inline-block shrink-0");
            
            return "<x-heroicon-o-$heroIcon class=\"$finalClasses\" />";
        }, $content);
        
        if ($replaced !== $content) {
            file_put_contents($file->getPathname(), $replaced);
            echo "Replaced in " . $file->getPathname() . "\n";
            $count++;
        }
    }
}
echo "Total files updated: $count\n";
