<?php
$files = [
    'resources/views/partials/sidebar-submenu.blade.php',
    'resources/views/partials/sidebar-link.blade.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Remove the 'heroicon-o-' . prefix from the dynamic component
    $content = str_replace(":component=\"'heroicon-o-' . \$icon\"", ':component="$icon"', $content);
    file_put_contents($file, $content);
}

// Update sidebar.blade.php with the specific icons
$sidebarFile = 'resources/views/partials/sidebar.blade.php';
$sidebarContent = file_get_contents($sidebarFile);

$sidebarContent = str_replace("'icon' => 'home'", "'icon' => 'heroicon-s-home'", $sidebarContent);
$sidebarContent = str_replace("'icon' => 'shopping-cart'", "'icon' => 'ionicon-cart-sharp'", $sidebarContent);
$sidebarContent = str_replace("'icon' => 'sparkles'", "'icon' => 'gmdi-menu-book-r'", $sidebarContent);
$sidebarContent = str_replace("'icon' => 'users'", "'icon' => 'gmdi-table-bar'", $sidebarContent);
$sidebarContent = str_replace("'icon' => 'cube'", "'icon' => 'bi-box-fill'", $sidebarContent);
$sidebarContent = str_replace("'icon' => 'truck'", "'icon' => 'polaris-order-draft-filled-icon'", $sidebarContent);
$sidebarContent = str_replace("'icon' => 'chart-bar'", "'icon' => 'iconoir-reports-solid'", $sidebarContent);

file_put_contents($sidebarFile, $sidebarContent);

echo "Sidebar updated.\n";
?>
