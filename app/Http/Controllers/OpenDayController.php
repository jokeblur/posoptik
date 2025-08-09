<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpenDay;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OpenDayController extends Controller
{
    // Open day (admin/superadmin)
    public function openDay(Request $request)
    {
        $user = auth()->user();
        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            abort(403, 'Hanya admin/superadmin yang boleh open day.');
        }
        $branch_id = $request->input('branch_id') ?: $user->branch_id;
        $today = now()->toDateString();
        Log::info('OpenDayController@openDay', ['branch_id' => $branch_id, 'tanggal' => $today, 'user_id' => $user->id]);
        try {
            $openDay = OpenDay::firstOrNew([
                'branch_id' => $branch_id,
                'tanggal' => $today,
            ]);
            $openDay->is_open = true;
            $openDay->created_at = $openDay->exists ? $openDay->created_at : now();
            $openDay->updated_at = now();
            $openDay->save();
            Log::info('OpenDayController@openDay SUCCESS', ['openDay' => $openDay->toArray()]);
            return response()->json(['success' => true, 'message' => 'Open day berhasil', 'openDay' => $openDay]);
        } catch (\Exception $e) {
            Log::error('OpenDayController@openDay ERROR', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal open day: ' . $e->getMessage()], 500);
        }
    }

    // Close day (admin/superadmin)
    public function closeDay(Request $request)
    {
        $user = auth()->user();
        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            abort(403, 'Hanya admin/superadmin yang boleh close day.');
        }
        $branch_id = $request->input('branch_id') ?: $user->branch_id;
        $today = now()->toDateString();
        Log::info('OpenDayController@closeDay', ['branch_id' => $branch_id, 'tanggal' => $today, 'user_id' => $user->id]);
        try {
            $openDay = OpenDay::where('branch_id', $branch_id)->where('tanggal', $today)->first();
            if (!$openDay || !$openDay->is_open) {
                Log::warning('OpenDayController@closeDay WARNING', ['message' => 'Belum open day atau sudah close day', 'branch_id' => $branch_id, 'tanggal' => $today]);
                return response()->json(['success' => false, 'message' => 'Belum open day atau sudah close day.'], 422);
            }
            $openDay->is_open = false;
            $openDay->updated_at = now();
            $openDay->save();
            Log::info('OpenDayController@closeDay SUCCESS', ['openDay' => $openDay->toArray()]);
            return response()->json(['success' => true, 'message' => 'Close day berhasil', 'openDay' => $openDay]);
        } catch (\Exception $e) {
            Log::error('OpenDayController@closeDay ERROR', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal close day: ' . $e->getMessage()], 500);
        }
    }

    // API status open day (kasir & admin)
    public function status(Request $request)
    {
        $user = auth()->user();
        $branch_id = $request->input('branch_id') ?: $user->branch_id;
        $today = now()->toDateString();
        $openDay = OpenDay::where('branch_id', $branch_id)->where('tanggal', $today)->first();
        return response()->json([
            'is_open' => $openDay && $openDay->is_open ? true : false,
            'open_time' => $openDay ? $openDay->created_at : null,
            'close_time' => $openDay && !$openDay->is_open ? $openDay->updated_at : null,
            'branch_id' => $branch_id,
            'tanggal' => $today,
        ]);
    }
} 