<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
$icons = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match_all('/<i\s+class="([^"]*?fa[sbr]?\s+fa-[a-zA-Z0-9\-]+[^"]*?|[^"]*?fa-(?:solid|brands|regular)\s+fa-[a-zA-Z0-9\-]+[^"]*?)"[^>]*><\/i>/i', $content, $matches)) {
            foreach ($matches[1] as $classes) {
                if (preg_match('/fa-(?!solid|brands|regular)([a-zA-Z0-9\-]+)/', $classes, $iconMatch)) {
                    $icons[$iconMatch[1]] = ($icons[$iconMatch[1]] ?? 0) + 1;
                }
            }
        }
    }
}

print_r($icons);
