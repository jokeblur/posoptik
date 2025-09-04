<?php
/**
 * Script untuk mengupdate semua halaman yang menggunakan tabel agar mobile responsive
 * Jalankan script ini di root directory Laravel
 */

echo "=== Updating All Tables for Mobile Responsive ===\n";

// Daftar file yang perlu diupdate
$filesToUpdate = [
    'resources/views/frame/index.blade.php',
    'resources/views/lensa/index.blade.php',
    'resources/views/penjualan/index.blade.php',
    'resources/views/sales/index.blade.php',
    'resources/views/user/index.blade.php',
    'resources/views/kategori/index.blade.php',
    'resources/views/dokter/index.blade.php',
    'resources/views/branch/index.blade.php',
    'resources/views/aksesoris/index.blade.php',
    'resources/views/passet/index.blade.php',
    'resources/views/barcode/index.blade.php',
    'resources/views/laporan/pos.blade.php',
    'resources/views/laporan/bpjs/index.blade.php',
    'resources/views/penjualan/signature-report.blade.php',
    'resources/views/inventory.blade.php'
];

$updatedCount = 0;
$errorCount = 0;

foreach ($filesToUpdate as $file) {
    if (!file_exists($file)) {
        echo "❌ File not found: $file\n";
        $errorCount++;
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Pattern 1: Update table-responsive wrapper
    $pattern1 = '/<div class="box-body table-responsive">\s*<table class="table[^"]*"([^"]*)"[^>]*>/';
    $replacement1 = '<div class="box-body">' . "\n" . 
                   '                @include(\'partials.mobile-table-wrapper\')' . "\n" . 
                   '                <table class="table table-striped table-bordered datatable"$1>';
    
    $content = preg_replace($pattern1, $replacement1, $content);
    
    // Pattern 2: Update table class to include datatable
    $pattern2 = '/<table class="table[^"]*"([^"]*)"[^>]*>/';
    if (strpos($content, 'datatable') === false) {
        $content = preg_replace_callback($pattern2, function($matches) {
            $class = $matches[1];
            if (strpos($class, 'datatable') === false) {
                return '<table class="table table-striped table-bordered datatable"' . $class . '>';
            }
            return $matches[0];
        }, $content);
    }
    
    // Pattern 3: Update DataTable initialization
    $pattern3 = '/\$\([\'"]#table[\'"]\)\.DataTable\(\{([^}]+)\}\)/';
    $replacement3 = '$("#table").DataTable({' . "\n" .
                   '            responsive: true,' . "\n" .
                   '            pageLength: $(window).width() <= 768 ? 5 : 10,' . "\n" .
                   '            language: {' . "\n" .
                   '                url: \'//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json\'' . "\n" .
                   '            },' . "\n" .
                   '            columnDefs: [' . "\n" .
                   '                { targets: \'_all\', defaultContent: \'-\' }' . "\n" .
                   '            ],' . "\n" .
                   '            drawCallback: function() {' . "\n" .
                   '                $(this).closest(\'.table-responsive\').addClass(\'table-responsive-mobile\');' . "\n" .
                   '            }' . "\n" .
                   '            $1' . "\n" .
                   '        })';
    
    $content = preg_replace($pattern3, $replacement3, $content);
    
    // Save file if changed
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "✅ Updated: $file\n";
            $updatedCount++;
        } else {
            echo "❌ Failed to update: $file\n";
            $errorCount++;
        }
    } else {
        echo "⏭️  No changes needed: $file\n";
    }
}

echo "\n=== Summary ===\n";
echo "✅ Files updated: $updatedCount\n";
echo "❌ Errors: $errorCount\n";
echo "📁 Total files processed: " . count($filesToUpdate) . "\n";

if ($updatedCount > 0) {
    echo "\n=== Next Steps ===\n";
    echo "1. Test the updated pages in mobile browser\n";
    echo "2. Clear Laravel cache: php artisan view:clear\n";
    echo "3. Deploy to VPS and test on real mobile devices\n";
}

echo "\n=== Mobile Responsive Tables Update Complete ===\n";
?>
