<?php

require_once 'vendor/autoload.php';

echo "Testing model loading...\n";

try {
    $penjualan = new App\Models\Penjualan();
    echo "Model Penjualan berhasil di-load!\n";
    echo "Class: " . get_class($penjualan) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
