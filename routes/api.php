<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenDayController;
use App\Http\Controllers\Api\V1\PosApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FrameApiController;
use App\Http\Controllers\Api\V1\PasienApiController;
use App\Http\Controllers\Api\V1\PenjualanApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/kasir-status', function (Request $request) {
    $user = $request->user();
    if ($user->isSuperAdmin() || $user->isAdmin()) {
        $branch_id = session('active_branch_id', $user->branch_id);
    } else {
        $branch_id = $user->branch_id;
    }
    $today = now('Asia/Jakarta')->toDateString();
    $openDay = \App\Models\OpenDay::where('branch_id', $branch_id)->where('tanggal', $today)->first();
    return response()->json([
        'is_open' => $openDay && $openDay->is_open ? true : false,
        'open_time' => $openDay ? $openDay->created_at : null,
        'close_time' => $openDay && !$openDay->is_open ? $openDay->updated_at : null,
        'branch_id' => $branch_id,
        'tanggal' => $today,
        'debug' => $openDay,
    ]);
});

Route::middleware('auth:sanctum')->get('/open-day-status', [OpenDayController::class, 'status']);

// API untuk mendapatkan daftar user
Route::middleware('auth:sanctum')->get('/users', function (Request $request) {
    try {
        $users = \App\Models\User::select('id', 'name', 'role', 'branch_id')
            ->orderBy('name')
            ->get();
        
        return response()->json($users);
    } catch (\Exception $e) {
        \Log::error('Error getting users: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to get users'], 500);
    }
});

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/frames', [FrameApiController::class, 'index']);
        Route::get('/frames/{frame}', [FrameApiController::class, 'show']);

        Route::get('/pasien', [PasienApiController::class, 'index']);
        Route::get('/pasien/{pasien}', [PasienApiController::class, 'show']);

        Route::get('/penjualan', [PenjualanApiController::class, 'index']);
        Route::get('/penjualan/{penjualan}', [PenjualanApiController::class, 'show']);

        Route::get('/search', [PosApiController::class, 'search']);
        Route::get('/reports/transactions', [PosApiController::class, 'report']);
        Route::get('/comments', [PosApiController::class, 'comments']);
        Route::post('/comments', [PosApiController::class, 'storeComment']);
    });
});