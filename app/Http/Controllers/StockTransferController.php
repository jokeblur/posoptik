<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
use App\Models\Branch;
use App\Models\Frame;
use App\Models\Lensa;
use App\Models\User;
use App\Exports\StockTransferExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StockTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of stock transfers
     */
    public function index()
    {
        $user = Auth::user();
        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'approvedBy'])
            ->accessibleByUser($user)
            ->latest()
            ->paginate(15);

        return view('stock-transfer.index', compact('transfers'));
    }

    /**
     * Display dashboard with transfer statistics
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get transfer statistics
        $totalTransfers = StockTransfer::accessibleByUser($user)->count();
        $pendingTransfers = StockTransfer::accessibleByUser($user)->where('status', 'Pending')->count();
        $completedTransfers = StockTransfer::accessibleByUser($user)->where('status', 'Completed')->count();
        $rejectedTransfers = StockTransfer::accessibleByUser($user)->where('status', 'Rejected')->count();
        
        // Get recent transfers
        $recentTransfers = StockTransfer::with(['fromBranch', 'toBranch'])
            ->accessibleByUser($user)
            ->latest()
            ->take(5)
            ->get();
        
        // Get branch statistics
        $branchStats = DB::table('stock_transfers')
            ->join('branches as from_branch', 'stock_transfers.from_branch_id', '=', 'from_branch.id')
            ->select(
                'from_branch.name as branch_name',
                DB::raw('COUNT(*) as total_transfers'),
                DB::raw('COUNT(CASE WHEN status = "Completed" THEN 1 END) as completed_transfers')
            )
            ->when(!$user->canAccessAllBranches(), function($query) use ($user) {
                return $query->where('from_branch_id', $user->branch_id);
            })
            ->groupBy('from_branch.id', 'from_branch.name')
            ->get();
        
        return view('stock-transfer.dashboard', compact(
            'totalTransfers',
            'pendingTransfers', 
            'completedTransfers',
            'rejectedTransfers',
            'recentTransfers',
            'branchStats'
        ));
    }

    /**
     * Show the form for creating a new stock transfer
     */
    public function create()
    {
        $user = Auth::user();
        $branches = Branch::active()->get();
        
        // Get available products from user's branch
        $frames = Frame::where('branch_id', $user->branch_id)
            ->where('stok', '>', 0)
            ->get();
        $lensas = Lensa::where('branch_id', $user->branch_id)
            ->where('stok', '>', 0)
            ->get();

        return view('stock-transfer.create', compact('branches', 'frames', 'lensas'));
    }

    /**
     * Store a newly created stock transfer
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.itemable_type' => 'required|in:App\Models\Frame,App\Models\Lensa',
            'items.*.itemable_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'to_branch_id.required' => 'Cabang tujuan harus dipilih',
            'to_branch_id.exists' => 'Cabang tujuan tidak valid',
            'items.required' => 'Minimal satu produk harus ditambahkan',
            'items.min' => 'Minimal satu produk harus ditambahkan',
            'items.*.itemable_type.required' => 'Jenis produk harus dipilih',
            'items.*.itemable_type.in' => 'Jenis produk tidak valid',
            'items.*.itemable_id.required' => 'Produk harus dipilih',
            'items.*.itemable_id.integer' => 'ID produk tidak valid',
            'items.*.quantity.required' => 'Jumlah harus diisi',
            'items.*.quantity.integer' => 'Jumlah harus berupa angka',
            'items.*.quantity.min' => 'Jumlah minimal adalah 1',
        ]);

        // Check if user is kasir
        if (!$user->isKasir()) {
            return back()->with('error', 'Hanya kasir yang dapat membuat permintaan transfer stok.');
        }

        // Check if destination branch is different from source
        if ($request->to_branch_id == $user->branch_id) {
            return back()->with('error', 'Tidak dapat transfer ke cabang yang sama.');
        }

        try {
            DB::beginTransaction();

            $transfer = StockTransfer::create([
                'kode_transfer' => StockTransfer::generateCode(),
                'from_branch_id' => $user->branch_id,
                'to_branch_id' => $request->to_branch_id,
                'requested_by' => $user->id,
                'status' => 'Pending',
                'notes' => $request->notes,
            ]);

            // Create transfer details
            foreach ($request->items as $item) {
                $product = $item['itemable_type']::find($item['itemable_id']);
                
                if (!$product || $product->branch_id !== $user->branch_id) {
                    throw new \Exception('Produk tidak valid atau tidak tersedia di cabang Anda.');
                }

                if ($product->stok < $item['quantity']) {
                    $productCode = $product->kode_frame ?? $product->kode_lensa;
                    throw new \Exception("Stok {$productCode} tidak mencukupi.");
                }

                StockTransferDetail::create([
                    'stock_transfer_id' => $transfer->id,
                    'itemable_type' => $item['itemable_type'],
                    'itemable_id' => $item['itemable_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->harga_beli_frame ?? $product->harga_beli_lensa ?? 0,
                    'total_price' => ($product->harga_beli_frame ?? $product->harga_beli_lensa ?? 0) * $item['quantity'],
                ]);
            }

            DB::commit();

            return redirect()->route('stock-transfer.index')
                ->with('success', 'Permintaan transfer stok berhasil dibuat dan menunggu persetujuan admin.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Stock transfer creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal membuat permintaan transfer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock transfer
     */
    public function show($id)
    {
        $user = Auth::user();
        $transfer = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'details.itemable'])
            ->accessibleByUser($user)
            ->findOrFail($id);

        return view('stock-transfer.show', compact('transfer'));
    }

    /**
     * Approve a stock transfer
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $transfer = StockTransfer::findOrFail($id);

        if (!$transfer->canBeApprovedBy($user)) {
            return back()->with('error', 'Anda tidak dapat menyetujui transfer ini.');
        }

        try {
            $transfer->approve($user);
            
            return back()->with('success', 'Transfer stok berhasil disetujui.');
        } catch (\Exception $e) {
            Log::error('Stock transfer approval failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyetujui transfer: ' . $e->getMessage());
        }
    }

    /**
     * Reject a stock transfer
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $transfer = StockTransfer::findOrFail($id);

        if (!$transfer->canBeApprovedBy($user)) {
            return back()->with('error', 'Anda tidak dapat menolak transfer ini.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $transfer->reject($user, $request->rejection_reason);
            
            return back()->with('success', 'Transfer stok berhasil ditolak.');
        } catch (\Exception $e) {
            Log::error('Stock transfer rejection failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menolak transfer: ' . $e->getMessage());
        }
    }

    /**
     * Complete a stock transfer
     */
    public function complete($id)
    {
        $user = Auth::user();
        $transfer = StockTransfer::findOrFail($id);

        if (!$transfer->canBeCompletedBy($user)) {
            return back()->with('error', 'Transfer tidak dapat diselesaikan.');
        }

        try {
            $transfer->complete();
            
            return back()->with('success', 'Transfer stok berhasil diselesaikan.');
        } catch (\Exception $e) {
            Log::error('Stock transfer completion failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyelesaikan transfer: ' . $e->getMessage());
        }
    }

    /**
     * Get products for transfer (AJAX)
     */
    public function getProducts(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $type = $request->type; // 'frame' or 'lensa'

        if ($type === 'frame') {
            $products = Frame::where('branch_id', $branchId)
                ->where('stok', '>', 0)
                ->get(['id', 'kode_frame', 'merk_frame', 'jenis_frame', 'stok', 'harga_beli_frame']);
        } else {
            $products = Lensa::where('branch_id', $branchId)
                ->where('stok', '>', 0)
                ->get(['id', 'kode_lensa', 'merk_lensa', 'type', 'stok', 'harga_beli_lensa']);
        }

        return response()->json($products);
    }

    /**
     * Cancel a stock transfer
     */
    public function cancel($id)
    {
        $user = Auth::user();
        $transfer = StockTransfer::findOrFail($id);

        // Only the requester or admin can cancel
        if ($transfer->requested_by !== $user->id && !$user->isAdmin() && !$user->isSuperAdmin()) {
            return back()->with('error', 'Anda tidak dapat membatalkan transfer ini.');
        }

        if ($transfer->status !== 'Pending') {
            return back()->with('error', 'Hanya transfer pending yang dapat dibatalkan.');
        }

        try {
            $transfer->update(['status' => 'Cancelled']);
            
            return back()->with('success', 'Transfer stok berhasil dibatalkan.');
        } catch (\Exception $e) {
            Log::error('Stock transfer cancellation failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal membatalkan transfer: ' . $e->getMessage());
        }
    }

    /**
     * Get transfer history for a specific branch
     */
    public function branchHistory(Request $request, $branchId)
    {
        $user = Auth::user();
        
        // Check if user can access this branch
        if (!$user->canAccessAllBranches() && $user->branch_id != $branchId) {
            return back()->with('error', 'Anda tidak dapat mengakses riwayat cabang ini.');
        }

        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'approvedBy'])
            ->where(function($query) use ($branchId) {
                $query->where('from_branch_id', $branchId)
                      ->orWhere('to_branch_id', $branchId);
            })
            ->latest()
            ->paginate(15);

        $branch = Branch::findOrFail($branchId);
        
        return view('stock-transfer.branch-history', compact('transfers', 'branch'));
    }

    /**
     * Export transfer data to Excel
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk export data.');
        }

        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'details'])
            ->accessibleByUser($user)
            ->latest()
            ->get();

        $filename = 'stock_transfers_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new StockTransferExport($transfers), $filename);
    }

    /**
     * Get transfer statistics for AJAX requests
     */
    public function getStats()
    {
        $user = Auth::user();
        
        $stats = [
            'total' => StockTransfer::accessibleByUser($user)->count(),
            'pending' => StockTransfer::accessibleByUser($user)->where('status', 'Pending')->count(),
            'completed' => StockTransfer::accessibleByUser($user)->where('status', 'Completed')->count(),
            'rejected' => StockTransfer::accessibleByUser($user)->where('status', 'Rejected')->count(),
            'cancelled' => StockTransfer::accessibleByUser($user)->where('status', 'Cancelled')->count(),
        ];
        
        return response()->json($stats);
    }
}
