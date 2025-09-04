<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenDayController;

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