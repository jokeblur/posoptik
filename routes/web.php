<?php

use App\Http\Controllers\FrameController;
use App\Http\Controllers\LensaController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\AksesorisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OpenDayController;
use App\Http\Controllers\LaporanPosController;
use App\Http\Controllers\StockTransferController;

use Illuminate\Routing\Console\MiddlewareMakeCommand;
use Illuminate\Support\Facades\Route;

use App\Exports\FrameExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LensaExport;
use App\Exports\LensaCsvExport;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', fn () =>redirect()->route ('login') );

// Route khusus untuk logout dengan redirect yang aman
Route::post('/logout', function () {
    try {
        // Logout user
        auth()->logout();
        
        // Invalidate session
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        // Clear all cookies
        if (request()->hasCookie('laravel_session')) {
            cookie()->forget('laravel_session');
        }
        
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout');
    } catch (\Exception $e) {
        \Log::error('Logout error: ' . $e->getMessage());
        return redirect()->route('login');
    }
})->name('logout');

// Alternative logout route (GET method for testing)
Route::get('/logout-direct', function () {
    try {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logout berhasil');
    } catch (\Exception $e) {
        \Log::error('Direct logout error: ' . $e->getMessage());
        return redirect()->route('login');
    }
})->name('logout.direct');

// Fallback route untuk menangani masalah akses setelah logout
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware([
    'auth:web',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Open/Close Day routes (baru)
    Route::post('/open-day', [OpenDayController::class, 'openDay'])->name('open.day');
    Route::post('/close-day', [OpenDayController::class, 'closeDay'])->name('close.day');
    Route::get('/openclose-day', [\App\Http\Controllers\OpenCloseDayController::class, 'index'])->name('openclose.day');

    Route::post('branches/set-active', [App\Http\Controllers\BranchController::class, 'setActive'])->name('branches.set_active');
    Route::get('branches/list', [App\Http\Controllers\BranchController::class, 'list'])->name('branches.list');
    
    // Penjualan routes - accessible by all authenticated users
    Route::get('penjualan/search_product', [App\Http\Controllers\PenjualanController::class, 'searchProduct'])->name('penjualan.search_product');
    Route::get('/penjualan/lensa-stok', [PenjualanController::class, 'getLensaStok'])->name('penjualan.lensa-stok');
    Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
    Route::get('/penjualan/statistics', [PenjualanController::class, 'statistics'])->name('penjualan.statistics');
    Route::get('/penjualan/{penjualan}/cetak', [PenjualanController::class, 'cetak'])->name('penjualan.cetak');
    Route::get('/penjualan/{penjualan}/cetak-half', [PenjualanController::class, 'cetakHalf'])->name('penjualan.cetak-half');
    Route::get('/pasien/{pasien}/cetak-resep', [PasienController::class, 'cetakResep'])->name('pasien.cetak-resep');
    Route::get('/pasien/{pasien}/cetak-resep-a4', [PasienController::class, 'cetakResepA4'])->name('pasien.cetak-resep-a4');
    Route::get('/pasien/{pasien}/cetak-resep-kartu', [PasienController::class, 'cetakResepKartu'])->name('pasien.cetak-resep-kartu');
    Route::post('/penjualan/{penjualan}/lunas', [PenjualanController::class, 'lunas'])->name('penjualan.lunas');
    Route::post('/penjualan/{id}/diambil', [PenjualanController::class, 'diambil'])->name('penjualan.diambil');
    Route::get('penjualan/omset-harian', [\App\Http\Controllers\PenjualanController::class, 'omsetHarian'])->name('penjualan.omset_harian');
    Route::post('/penjualan/calculate-bpjs-price', [PenjualanController::class, 'calculateBpjsPrice'])->name('penjualan.calculate_bpjs_price');

    Route::post('/penjualan/test-bpjs-pricing', [PenjualanController::class, 'testBpjsPricing'])->name('penjualan.test_bpjs_pricing');
    Route::post('/penjualan/debug-frame-data', [PenjualanController::class, 'debugFrameData'])->name('penjualan.debug_frame_data');
    Route::post('/penjualan/fix-bpjs-prices', [PenjualanController::class, 'fixBpjsPrices'])->name('penjualan.fix_bpjs_prices');
    Route::post('/penjualan/{id}/update-status-pengerjaan', [PenjualanController::class, 'updateStatusPengerjaan'])->name('penjualan.update_status_pengerjaan');
    Route::resource('penjualan', App\Http\Controllers\PenjualanController::class);

    Route::get('/laporan-pos', [App\Http\Controllers\LaporanPosController::class, 'index'])->name('laporan.pos')->middleware('role:admin,super admin');
    Route::get('/laporan-pos/data', [App\Http\Controllers\LaporanPosController::class, 'getData'])->name('laporan.pos.data')->middleware('role:admin,super admin');
    
    // Laporan BPJS routes
    Route::get('/laporan-bpjs', [App\Http\Controllers\LaporanBpjsController::class, 'index'])->name('laporan.bpjs')->middleware('role:admin,super admin');
    Route::get('/laporan-bpjs/data', [App\Http\Controllers\LaporanBpjsController::class, 'data'])->name('laporan.bpjs.data')->middleware('role:admin,super admin');
    Route::get('/laporan-bpjs/summary', [App\Http\Controllers\LaporanBpjsController::class, 'summary'])->name('laporan.bpjs.summary')->middleware('role:admin,super admin');
    Route::get('/laporan-bpjs/export', [App\Http\Controllers\LaporanBpjsController::class, 'export'])->name('laporan.bpjs.export')->middleware('role:admin,super admin');
    
    // Laporan Tanda Tangan BPJS routes
    Route::get('/laporan-signature-bpjs', [App\Http\Controllers\PenjualanController::class, 'signatureReport'])->name('laporan.signature.bpjs')->middleware('role:admin,super admin');
    Route::get('/laporan-signature-bpjs/data', [App\Http\Controllers\PenjualanController::class, 'signatureReportData'])->name('laporan.signature.bpjs.data')->middleware('role:admin,super admin');
    
    // API untuk dashboard charts
    Route::get('/api/dashboard/chart-data', [App\Http\Controllers\DashboardController::class, 'getChartData'])->name('dashboard.chart-data')->middleware('role:admin,super admin');
    
    // Real-time endpoints
    Route::get('/realtime/dashboard', [App\Http\Controllers\RealtimeController::class, 'dashboard'])->name('realtime.dashboard');
    Route::get('/realtime/omset-kasir', [App\Http\Controllers\RealtimeController::class, 'omsetKasir'])->name('realtime.omset-kasir');
    Route::get('/realtime/notifications', [App\Http\Controllers\RealtimeController::class, 'notifications'])->name('realtime.notifications');
    Route::get('/realtime/stock-updates', [App\Http\Controllers\RealtimeController::class, 'stockUpdates'])->name('realtime.stock-updates');
    
    // Barcode routes - scan direct tidak perlu auth
    Route::get('/barcode/scan/{barcode}', [App\Http\Controllers\BarcodeController::class, 'scanDirect'])->name('barcode.scan.direct');
    
    // Barcode routes yang memerlukan auth
    Route::get('/barcode/scan', [App\Http\Controllers\BarcodeController::class, 'scan'])->name('barcode.scan');
    Route::get('/barcode/scan-mobile', function() {
        return view('barcode.scan_mobile');
    })->name('barcode.scan.mobile');
    Route::post('/barcode/search', [App\Http\Controllers\BarcodeController::class, 'search'])->name('barcode.search');
    Route::post('/barcode/update-status', [App\Http\Controllers\BarcodeController::class, 'updateStatus'])->name('barcode.update-status');
    Route::post('/barcode/generate', [App\Http\Controllers\BarcodeController::class, 'generateBarcode'])->name('barcode.generate');
    Route::get('/barcode/print/{id}', [App\Http\Controllers\BarcodeController::class, 'printBarcode'])->name('barcode.print');
    Route::post('/barcode/bulk-generate', [App\Http\Controllers\BarcodeController::class, 'bulkGenerateBarcode'])->name('barcode.bulk-generate')->middleware('role:admin,super admin');
    
    // Barcode index route
    Route::get('/barcode', [App\Http\Controllers\BarcodeController::class, 'index'])->name('barcode.index')->middleware('role:admin,super admin');
    
    // Stock Transfer routes
                   Route::get('/stock-transfer/dashboard', [StockTransferController::class, 'dashboard'])->name('stock-transfer.dashboard');
               Route::get('/stock-transfer/stats', [StockTransferController::class, 'getStats'])->name('stock-transfer.stats');
               Route::get('/stock-transfer/products', [StockTransferController::class, 'getProducts'])->name('stock-transfer.products');
               Route::post('/stock-transfer/{id}/approve', [StockTransferController::class, 'approve'])->name('stock-transfer.approve')->middleware('role:admin,super admin');
               Route::post('/stock-transfer/{id}/reject', [StockTransferController::class, 'reject'])->name('stock-transfer.reject')->middleware('role:admin,super admin');
               Route::post('/stock-transfer/{id}/complete', [StockTransferController::class, 'complete'])->name('stock-transfer.complete');
               Route::post('/stock-transfer/{id}/cancel', [StockTransferController::class, 'cancel'])->name('stock-transfer.cancel');
               Route::get('/stock-transfer/branch/{branchId}/history', [StockTransferController::class, 'branchHistory'])->name('stock-transfer.branch-history');
               Route::get('/stock-transfer/export', [StockTransferController::class, 'export'])->name('stock-transfer.export')->middleware('role:admin,super admin');
               Route::resource('stock-transfer', StockTransferController::class);
    
    // Test route untuk QR Code
    Route::get('/test-qrcode', function() {
        return view('test_qrcode');
    })->name('test.qrcode');
});

Route::group(['middleware' => 'auth'], function() {
    // Branch routes (accessible by authenticated users)
    Route::get('/branch/data', [BranchController::class, 'data'])->name('branch.data');
    Route::resource('/branch', BranchController::class);
    
    // Branch switching and user branches (for all authenticated users)
    Route::post('/branch/switch', [BranchController::class, 'switchBranch'])->name('branch.switch');
    Route::get('/branch/user-branches', [BranchController::class, 'getUserBranches'])->name('branch.user-branches');

    Route::get('/frame/data', [FrameController::class, 'data'])->name('frame.data');
    Route::post('/frame/bulk-delete', [FrameController::class, 'bulkDelete'])->name('frame.bulk-delete');
    Route::resource('/frame', FrameController::class);

    Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');    
    Route::resource('/kategori', KategoriController::class);

    Route::get('/lensa/data', [LensaController::class, 'data'])->name('lensa.data');
    Route::get('/lensa/data/{branch}', [LensaController::class, 'dataByBranch'])->name('lensa.data.branch');
    Route::post('/lensa/store', [LensaController::class, 'store'])->name('lensa.store');
    Route::post('/lensa/bulk-delete', [LensaController::class, 'bulkDelete'])->name('lensa.bulk-delete');
    Route::post('/lensa/import', [LensaController::class, 'import'])->name('lensa.import');
    Route::get('/lensa/export', [LensaController::class, 'export'])->name('lensa.export');
    Route::get('/lensa/template', [LensaController::class, 'downloadTemplate'])->name('lensa.template');
    Route::resource('/lensa', LensaController::class);

    Route::get('/sales/data', [SalesController::class, 'data'])->name('sales.data');    
    Route::post('/sales/import', [SalesController::class, 'import'])->name('sales.import');
    Route::get('/sales/export', [SalesController::class, 'export'])->name('sales.export');
    Route::get('/sales/export-full', [SalesController::class, 'exportFull'])->name('sales.export-full');
    Route::resource('/sales', SalesController::class);

    Route::get('/dokter/data', [DokterController::class, 'data'])->name('dokter.data');    
    Route::resource('/dokter', DokterController::class);

    Route::get('/pasien/export', [PasienController::class, 'export'])->name('pasien.export');
    Route::post('/pasien/import', [PasienController::class, 'import'])->name('pasien.import');
    Route::post('/pasien/bulk-delete', [PasienController::class, 'bulkDelete'])->name('pasien.bulk-delete');
    Route::get('/pasien/data', [PasienController::class, 'data'])->name('pasien.data');
    Route::get('/pasien/{id}/details', [PasienController::class, 'getDetails'])->name('pasien.details');
    Route::post('/pasien/store-and-redirect', [PasienController::class, 'storeAndRedirect'])->name('pasien.store-and-redirect');
    Route::resource('/pasien', PasienController::class);

    Route::get('/transaksi/data-lensa', [PenjualanController::class, 'getLensa']);
    Route::get('/transaksi/data-frame', [PenjualanController::class, 'getFrame']);

    Route::get('/inventory', function () {
        $user = auth()->user();
        $lensas = \App\Models\Lensa::accessibleByUser($user)->get();
        $frames = \App\Models\Frame::accessibleByUser($user)->get();
        
        return view('inventory', [
            'lensas' => $lensas,
            'frames' => $frames
        ]);
    });
    
    // Route::post('/lensa/store', [LensaController::class, 'store'])->name('lensa.store');
    Route::post('/frame/store', [FrameController::class, 'store'])->name('frame.store');
    Route::post('/frame/import', [FrameController::class, 'import'])->name('frame.import');
    Route::get('/frame/export', [FrameController::class, 'export'])->name('frame.export');
    Route::get('/frame/export-full', [FrameController::class, 'exportFull'])->name('frame.export-full');
    Route::get('/frame/template', [FrameController::class, 'downloadTemplate'])->name('frame.template');
    
    // Passet routes
    Route::get('/passet/data', [App\Http\Controllers\PassetController::class, 'data'])->name('passet.data')->middleware('role:passet bantu,admin,super admin');
    Route::get('/passet', [App\Http\Controllers\PassetController::class, 'index'])->name('passet.index')->middleware('role:passet bantu,admin,super admin');
    Route::post('/passet/{id}/selesai', [App\Http\Controllers\PassetController::class, 'markAsSelesai'])->name('passet.selesai')->middleware('role:passet bantu,admin,super admin');
    
    // User Management
    Route::get('/user/data', [\App\Http\Controllers\UserController::class, 'data'])->name('user.data')->middleware('role:admin,super admin');
    Route::resource('/user', \App\Http\Controllers\UserController::class)->except(['create', 'edit'])->middleware('role:admin,super admin');


    Route::resource('/aksesoris', App\Http\Controllers\AksesorisController::class);
});

// Route untuk get users list (di luar group middleware untuk menghindari konflik)
Route::get('/get-passet-users', [PenjualanController::class, 'getUsersList'])->name('penjualan.users_list')->middleware('auth');

// Route test export tanpa middleware
Route::get('/test-export', function() {
    try {
        return Excel::download(new FrameExport, 'test_frame.xlsx');
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-export-lensa', function() {
    try {
        return Excel::download(new LensaExport, 'test_lensa.xlsx');
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-export-simple', function() {
    try {
        $data = collect([
            ['Name', 'Email', 'Phone'],
            ['John Doe', 'john@example.com', '123456789'],
            ['Jane Smith', 'jane@example.com', '987654321'],
        ]);

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
            public function headings(): array { return ['Name', 'Email', 'Phone']; }
        }, 'test_simple.xlsx');
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-export-array', function() {
    try {
        return Excel::download(new \App\Exports\SimpleFrameExport, 'test_simple_frame.xlsx');
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-export-lensa-simple', function() {
    try {
        return Excel::download(new \App\Exports\SimpleLensaExport, 'test_simple_lensa.xlsx');
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-export-frame-real', function() {
    try {
        return Excel::download(new \App\Exports\FrameExport, 'test_frame_real.xlsx');
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-export-lensa-real', function() {
    try {
        return Excel::download(new \App\Exports\LensaExport, 'test_lensa_real.xlsx');
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-export-lensa-csv', function() {
    try {
        return Excel::download(new LensaCsvExport, 'test_lensa.csv', \Maatwebsite\Excel\Excel::CSV);
    } catch (\Exception $e) {
        return response('CSV Export error: ' . $e->getMessage(), 500);
    }
});

// Test logout functionality
Route::get('/test-logout', function() {
    return view('test_logout');
})->name('test.logout');

Route::post('/test-import-simple', function(\Illuminate\Http\Request $request) {
    try {
        // Log request info
        \Log::info('Test import request received');
        \Log::info('Request has file: ' . ($request->hasFile('file') ? 'YES' : 'NO'));
        
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan dalam request'
            ], 400);
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        \Log::info('Test import file info:', [
            'filename' => $request->file('file')->getClientOriginalName(),
            'size' => $request->file('file')->getSize(),
            'mime' => $request->file('file')->getMimeType()
        ]);

        // Test import dengan SimpleFrameImport
        Excel::import(new \App\Imports\SimpleFrameImport, $request->file('file'));
        
        return response()->json([
            'success' => true,
            'message' => 'Test import berhasil!'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Test import validation error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error validasi: ' . $e->getMessage()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Test import error: ' . $e->getMessage());
        \Log::error('Test import error trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Test import gagal: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/test-import-page', function() {
    return view('test-import');
});

Route::post('/test-upload', function(\Illuminate\Http\Request $request) {
    try {
        // Test upload sederhana tanpa Excel
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 400);
        }

        $file = $request->file('file');
        
        return response()->json([
            'success' => true,
            'message' => 'File berhasil diupload',
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension()
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

Route::post('/test-import-lensa', function(\Illuminate\Http\Request $request) {
    try {
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan dalam request'
            ], 400);
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        \Log::info('Test lensa import file info:', [
            'filename' => $request->file('file')->getClientOriginalName(),
            'size' => $request->file('file')->getSize(),
            'mime' => $request->file('file')->getMimeType()
        ]);

        Excel::import(new \App\Imports\SimpleLensaImport, $request->file('file'));
        
        return response()->json([
            'success' => true,
            'message' => 'Test import lensa berhasil!'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Test lensa import validation error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error validasi: ' . $e->getMessage()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Test lensa import error: ' . $e->getMessage());
        \Log::error('Test lensa import error trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Test import lensa gagal: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/test-template-frame', function() {
    try {
        return Excel::download(
            new \App\Exports\FrameTemplateExport, 
            'test_template_frame.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    } catch (\Exception $e) {
        return response('Template download error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-template-lensa', function() {
    try {
        \Log::info('Test template lensa download started');
        
        // Direct template creation to avoid class dependency issues
        $headers = [
            'Kode Lensa',
            'Merk Lensa', 
            'Type',
            'Index',
            'Coating',
            'Harga Beli',
            'Harga Jual',
            'Stok',
            'Cabang',
            'Sales',
            'Tipe Stok',
            'Catatan'
        ];
        
        $sampleData = [
            [
                'L00001',
                'Essilor',
                'Single Vision',
                '1.56',
                'Anti-Reflective',
                200000,
                300000,
                20,
                'Cabang Utama',
                'John Sales',
                'Ready Stock',
                'Lensa premium kualitas tinggi'
            ],
            [
                'L00002',
                'Hoya', 
                'Progressive',
                '1.67',
                'Blue Cut',
                400000,
                600000,
                15,
                'Cabang Utama',
                'Jane Sales',
                'Custom Order',
                'Lensa progresif untuk presbyopia'
            ],
            array_fill(0, 12, '') // Empty row
        ];
        
        // Create export data
        $data = array_merge([$headers], $sampleData);
        
        // Create anonymous export class
        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            
            public function __construct($data) {
                $this->data = $data;
            }
            
            public function array(): array {
                return $this->data;
            }
        };
        
        \Log::info('Test template lensa export created successfully');
        
        return Excel::download(
            $export,
            'template_lensa_updated.xlsx'
        );
    } catch (\Exception $e) {
        \Log::error('Test template lensa error: ' . $e->getMessage());
        return response('Template download error: ' . $e->getMessage(), 500);
    }
});

Route::get('/template-simple-frame', function() {
    try {
        return Excel::download(
            new \App\Exports\SimpleTemplateExport('frame'), 
            'template_frame_simple.xlsx'
        );
    } catch (\Exception $e) {
        return response('Template download error: ' . $e->getMessage(), 500);
    }
});

Route::get('/template-simple-lensa', function() {
    try {
        return Excel::download(
            new \App\Exports\SimpleTemplateExport('lensa'), 
            'template_lensa_simple.xlsx'
        );
    } catch (\Exception $e) {
        return response('Template download error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-excel', function() {
    try {
        $data = [
            ['Kode', 'Nama', 'Harga'],
            ['001', 'Produk A', 100000],
            ['002', 'Produk B', 200000],
        ];
        
        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
            public function headings(): array { return ['Kode', 'Nama', 'Harga']; }
        }, 'test_excel.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

// Test route untuk debug export lensa
Route::get('/test-lensa-export-debug', function() {
    try {
        Log::info('Testing lensa export debug');
        
        // Test authentication
        $user = auth()->user();
        if (!$user) {
            Log::warning('No authenticated user for lensa export test');
            return response('No authenticated user', 401);
        }
        
        Log::info('User authenticated: ' . $user->id);
        
        // Test export class
        $export = new \App\Exports\LensaExport();
        $data = $export->array();
        
        Log::info('Export data count: ' . count($data));
        
        if (empty($data)) {
            return response('No data to export', 404);
        }
        
        // Test Excel generation
        $filename = 'test_lensa_debug_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        Log::info('Generating Excel file: ' . $filename);
        
        return Excel::download($export, $filename);
        
    } catch (\Exception $e) {
        Log::error('Lensa export debug error: ' . $e->getMessage());
        Log::error('Lensa export debug trace: ' . $e->getTraceAsString());
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

// Test route untuk export lensa tanpa auth - format import
Route::get('/test-lensa-export-simple', function() {
    try {
        // Get all lensas without auth
        $lensas = \App\Models\Lensa::with(['branch', 'sales'])->orderBy('id', 'desc')->limit(5)->get();
        
        $result = $lensas->map(function($lensa) {
            return [
                (string) ($lensa->kode_lensa ?? ''),
                (string) ($lensa->merk_lensa ?? ''),
                (string) ($lensa->type ?? ''),
                (string) ($lensa->index ?? ''),
                (string) ($lensa->coating ?? ''),
                (string) ($lensa->harga_beli_lensa ?? '0'),
                (string) ($lensa->harga_jual_lensa ?? '0'),
                (string) ($lensa->stok ?? '0'),
                (string) ($lensa->branch ? $lensa->branch->name : ''),
                (string) ($lensa->sales ? $lensa->sales->nama_sales : ''),
                (string) ($lensa->is_custom_order ? 'Custom Order' : 'Ready Stock'),
                (string) ($lensa->add ?? ''),
            ];
        });
        
        $export = new class($result) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
            public function headings(): array { 
                return [
                    'Kode Lensa',
                    'Merk Lensa', 
                    'Type',
                    'Index',
                    'Coating',
                    'Harga Beli',
                    'Harga Jual',
                    'Stok',
                    'Cabang',
                    'Sales',
                    'Tipe Stok',
                    'Catatan'
                ]; 
            }
        };
        
        return Excel::download($export, 'test_lensa_importable.xlsx');
        
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});

// Test route untuk export data stok test
Route::get('/test-stok-export', function() {
    try {
        $data = [
            [
                'Kode Lensa' => 'TEST001',
                'Merk Lensa' => 'Test Brand',
                'Type' => 'Progressive',
                'Index' => '1.56',
                'Coating' => 'Anti UV',
                'Harga Beli' => '50000',
                'Harga Jual' => '75000',
                'Stok' => '10',
                'Cabang' => 'Optik Melati Cabang 1',
                'Sales' => 'Test Sales',
                'Tipe Stok' => 'Ready Stock',
                'Catatan' => 'Test note'
            ],
            [
                'Kode Lensa' => 'TEST002',
                'Merk Lensa' => 'Test Brand 2',
                'Type' => 'Bifokal',
                'Index' => '1.67',
                'Coating' => 'Anti UV',
                'Harga Beli' => '60000',
                'Harga Jual' => '90000',
                'Stok' => '5',
                'Cabang' => 'Optik Melati Cabang 1',
                'Sales' => 'Test Sales',
                'Tipe Stok' => 'Ready Stock',
                'Catatan' => 'Test note 2'
            ]
        ];
        
        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        };
        
        return Excel::download($export, 'test_stok_import.xlsx');
        
    } catch (\Exception $e) {
        return response('Export error: ' . $e->getMessage(), 500);
    }
});