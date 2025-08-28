<?php

require_once 'vendor/autoload.php';

use App\Models\Penjualan;

try {
    $penjualan = new Penjualan();
    echo "Model Penjualan berhasil di-load!\n";
    echo "Class: " . get_class($penjualan) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
