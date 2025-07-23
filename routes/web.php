<?php

use App\Http\Controllers\FrameController;
use App\Http\Controllers\LensaController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\BranchController;

use Illuminate\Routing\Console\MiddlewareMakeCommand;
use Illuminate\Support\Facades\Route;


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

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('home');
    })->name('dashboard');

    Route::post('branches/set_active', [App\Http\Controllers\BranchController::class, 'setActive'])->name('branches.set_active');
    Route::get('branches/list', [App\Http\Controllers\BranchController::class, 'list'])->name('branches.list');
    Route::get('penjualan/search_product', [App\Http\Controllers\PenjualanController::class, 'searchProduct'])->name('penjualan.search_product');
    Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
    Route::get('/penjualan/{penjualan}/cetak', [PenjualanController::class, 'cetak'])->name('penjualan.cetak');
    Route::post('/penjualan/{penjualan}/lunas', [PenjualanController::class, 'lunas'])->name('penjualan.lunas');
    Route::resource('penjualan', App\Http\Controllers\PenjualanController::class);
});

Route::group(['middleware' => 'auth'], function() {
    // Branch routes (accessible by authenticated users)
    Route::get('/branch/data', [BranchController::class, 'data'])->name('branch.data');
    Route::resource('/branch', BranchController::class);
    
    // Branch switching and user branches (for all authenticated users)
    Route::post('/branch/switch', [BranchController::class, 'switchBranch'])->name('branch.switch');
    Route::get('/branch/user-branches', [BranchController::class, 'getUserBranches'])->name('branch.user-branches');

    Route::get('/frame/data', [FrameController::class, 'data'])->name('frame.data');
    Route::resource('/frame', FrameController::class);

    Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');    
    Route::resource('/kategori', KategoriController::class);

    Route::get('/lensa/data', [LensaController::class, 'data'])->name('lensa.data');
    Route::get('/lensa/data/{branch}', [LensaController::class, 'dataByBranch'])->name('lensa.data.branch');
    Route::post('/lensa/store', [LensaController::class, 'store'])->name('lensa.store');
    Route::resource('/lensa', LensaController::class);

    Route::get('/sales/data', [SalesController::class, 'data'])->name('sales.data');    
    Route::resource('/sales', SalesController::class);

    Route::get('/dokter/data', [DokterController::class, 'data'])->name('dokter.data');    
    Route::resource('/dokter', DokterController::class);

    Route::get('/pasien/data', [PasienController::class, 'data'])->name('pasien.data');
    Route::get('/pasien/{id}/details', [PasienController::class, 'getDetails'])->name('pasien.details');
    Route::resource('/pasien', PasienController::class);

    // Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');    
    // Route::resource('/penjualan', PenjualanController::class);

    Route::get('/transaksi/data-lensa', [PenjualanController::class, 'getLensa']);
    Route::get('/transaksi/data-frame', [PenjualanController::class, 'getFrame']);



    Route::get('/transaksi/baru', [PenjualanController::class, 'create'])->name('transaksi.baru');  
    Route::resource('/transaksi', PenjualanController::class)
    ->except('show');
    

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
    Route::post('/lensa/import', [LensaController::class, 'import'])->name('lensa.import');
    Route::post('/frame/import', [FrameController::class, 'import'])->name('frame.import');
    
    // Passet routes
    Route::get('/passet/data', [App\Http\Controllers\PassetController::class, 'data'])->name('passet.data')->middleware('role:passet bantu,admin,super admin');
    Route::get('/passet', [App\Http\Controllers\PassetController::class, 'index'])->name('passet.index')->middleware('role:passet bantu,admin,super admin');
    Route::post('/passet/{id}/selesai', [App\Http\Controllers\PassetController::class, 'markAsSelesai'])->name('passet.selesai')->middleware('role:passet bantu,admin,super admin');
    
    // User Management
    Route::get('/user/data', [\App\Http\Controllers\UserController::class, 'data'])->name('user.data')->middleware('role:admin,super admin');
    Route::resource('/user', \App\Http\Controllers\UserController::class)->except(['create', 'edit'])->middleware('role:admin,super admin');

    Route::get('/lensa/export', [LensaController::class, 'export'])->name('lensa.export');
    Route::get('/frame/export', [FrameController::class, 'export'])->name('frame.export');
});

Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
Route::post('/penjualan/{id}/lunas', [PenjualanController::class, 'lunas'])->name('penjualan.lunas');
Route::post('/penjualan/{id}/diambil', [PenjualanController::class, 'diambil'])->name('penjualan.diambil');
Route::get('/penjualan/cetak/{id}', [PenjualanController::class, 'cetak'])->name('penjualan.cetak');
Route::get('/penjualan/{penjualan}', [PenjualanController::class, 'show'])->name('penjualan.show');
Route::post('/penjualan/store', [PenjualanController::class, 'store'])->name('penjualan.store');

// Stok routes


