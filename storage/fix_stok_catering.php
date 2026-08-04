<?php

$operasional = file_get_contents('/Applications/XAMPP/xamppfiles/htdocs/bbc-resto/resources/views/inventory/stok-operasional/index.blade.php');
$catering = file_get_contents('/Applications/XAMPP/xamppfiles/htdocs/bbc-resto/resources/views/inventory/stok-catering/index.blade.php');

$filterStart = strpos($operasional, '{{-- Filter Bar --}}');
$operasionalContent = substr($operasional, $filterStart);

// Replace route
$operasionalContent = str_replace("route('stok-operasional.index')", "route('stok-catering.index')", $operasionalContent);

$cateringFilterStart = strpos($catering, '{{-- Filter Bar --}}');
$newCatering = substr($catering, 0, $cateringFilterStart) . $operasionalContent;

file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/bbc-resto/resources/views/inventory/stok-catering/index.blade.php', $newCatering);

echo "Success!\n";
