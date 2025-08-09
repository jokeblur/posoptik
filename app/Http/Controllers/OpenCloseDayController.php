<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\OpenDay;
use Carbon\Carbon;

class OpenCloseDayController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            abort(403, 'Hanya super admin dan admin yang boleh mengakses menu ini.');
        }
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $branches = Branch::all();
        $openDays = OpenDay::where('tanggal', $today)->get()->keyBy('branch_id');
        return view('openclose_day.index', compact('branches', 'openDays', 'today'));
    }
} 