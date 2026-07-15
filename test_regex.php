<?php
$content = '<i class="fa-solid fa-arrow-left mr-1 text-red-500"></i> <i class="fa-brands fa-whatsapp"></i> <i class="text-sm fa-solid fa-eye"></i>';

$mapping = [
    'arrow-left' => 'arrow-left',
    'whatsapp' => 'chat-bubble-left-ellipsis',
    'eye' => 'eye',
    // ...
];

$replaced = preg_replace_callback('/<i\s+class="([^"]*?fa-(?:solid|brands|regular)\s+fa-[a-zA-Z0-9\-]+[^"]*?)"[^>]*><\/i>/i', function($matches) use ($mapping) {
    $classes = $matches[1];
    
    // Extract the specific FA icon name
    preg_match('/fa-([a-zA-Z0-9\-]+)/', $classes, $iconMatch);
    $faIcon = $iconMatch[1];
    
    // Remove the fa-* classes to leave only utility classes
    $utilityClasses = preg_replace('/fa-(?:solid|brands|regular)\s+fa-[a-zA-Z0-9\-]+/', '', $classes);
    $utilityClasses = trim(preg_replace('/\s+/', ' ', $utilityClasses));
    
    // Determine replacement icon
    $heroIcon = $mapping[$faIcon] ?? 'star'; // default fallback
    
    // Append w-5 h-5 if no w- h- classes exist? Maybe just append them
    $finalClasses = trim("$utilityClasses w-5 h-5 inline-block");
    
    return "<x-heroicon-o-$heroIcon class=\"$finalClasses\" />";
}, $content);

echo $replaced . "\n";
