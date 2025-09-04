<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Penjualan;
use App\Models\OpenDay;
use App\Services\BpjsPricingService;
use Carbon\Carbon;

class RealtimeController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        // Set execution time limit
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        
        return response()->stream(function () use ($user) {
            $lastCheck = now();
            $heartbeatCount = 0;
            
            while (true) {
                // Send heartbeat every 30 seconds to keep connection alive
                if ($heartbeatCount % 6 == 0) {
                    echo "data: " . json_encode(['type' => 'heartbeat', 'timestamp' => now()->toISOString()]) . "\n\n";
                }
                
                // Get real-time data
                $data = $this->getDashboardData($user);
                $data['type'] = 'dashboard_update';
                $data['timestamp'] = now()->toISOString();
                
                // Send data as SSE
                echo "data: " . json_encode($data) . "\n\n";
                
                // Flush output
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
                
                // Update last check time
                $lastCheck = now();
                $heartbeatCount++;
                
                // Wait 5 seconds before next update
                sleep(5);
                
                // Check if connection is still alive
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no', // Disable Nginx buffering
            'Connection' => 'keep-alive',
        ]);
    }
    
    public function omsetKasir(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isKasir()) {
            abort(403, 'Unauthorized');
        }
        
        // Set execution time limit
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        
        return response()->stream(function () use ($user) {
            $heartbeatCount = 0;
            
            while (true) {
                // Send heartbeat every 30 seconds to keep connection alive
                if ($heartbeatCount % 10 == 0) {
                    echo "data: " . json_encode(['type' => 'heartbeat', 'timestamp' => now()->toISOString()]) . "\n\n";
                }
                
                $data = $this->getOmsetKasirData($user);
                $data['type'] = 'omset_update';
                $data['timestamp'] = now()->toISOString();
                
                echo "data: " . json_encode($data) . "\n\n";
                
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
                
                $heartbeatCount++;
                sleep(3); // Update setiap 3 detik untuk omset
                
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
    
    private function getDashboardData($user)
    {
        $today = now()->toDateString();
        $selectedBranchId = $user->isSuperAdmin() ? null : $user->branch_id;
        
        $openDay = OpenDay::where('branch_id', $selectedBranchId)
            ->where('tanggal', $today)
            ->first();
            
        $omsetStart = null;
        $omsetEnd = null;
        if ($openDay) {
            $omsetStart = $openDay->created_at;
            if (!$openDay->is_open) {
                $omsetEnd = $openDay->updated_at;
            } else {
                $omsetEnd = now();
            }
        }
        
        $data = [
            'timestamp' => now()->toISOString(),
            'open_day_status' => $openDay ? $openDay->is_open : false,
            'open_time' => $openDay ? $openDay->created_at->format('H:i') : null,
        ];
        
        if ($user->isKasir()) {
            $data = array_merge($data, $this->getOmsetKasirData($user));
        } else if ($user->isSuperAdmin() || $user->isAdmin()) {
            $data = array_merge($data, $this->getAdminData($user, $selectedBranchId, $omsetStart, $omsetEnd));
        }
        
        return $data;
    }
    
    private function getOmsetKasirData($user)
    {
        $today = now()->toDateString();
        $selectedBranchId = $user->branch_id;
        
        $openDay = OpenDay::where('branch_id', $selectedBranchId)
            ->where('tanggal', $today)
            ->first();
            
        $omsetStart = null;
        $omsetEnd = null;
        if ($openDay) {
            $omsetStart = $openDay->created_at;
            if (!$openDay->is_open) {
                $omsetEnd = $openDay->updated_at;
            } else {
                $omsetEnd = now();
            }
        }
        
        $omsetKasir = Penjualan::where('branch_id', $selectedBranchId)
            ->where('user_id', $user->id)
            ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
            ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
            ->sum('total');
            
        // Hitung omset BPJS berdasarkan harga default layanan BPJS
        $bpjsTransactions = Penjualan::where('branch_id', $selectedBranchId)
            ->where('user_id', $user->id)
            ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
            ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
            ->whereHas('pasien', function($q) {
                $q->whereIn('service_type', ['BPJS I', 'BPJS II', 'BPJS III']);
            })
            ->with('pasien')
            ->get();
            
        $omsetBpjs = $bpjsTransactions->sum(function($transaksi) {
            $serviceType = $transaksi->pasien->service_type ?? 'UMUM';
            
            // Gunakan harga default BPJS sesuai jenis layanan
            switch ($serviceType) {
                case 'BPJS I':
                    return BpjsPricingService::BPJS_I_PRICE;
                case 'BPJS II':
                    return BpjsPricingService::BPJS_II_PRICE;
                case 'BPJS III':
                    return BpjsPricingService::BPJS_III_PRICE;
                default:
                    return 0;
            }
        });
        
        // Debug logging untuk membantu troubleshooting
        \Log::info('RealtimeController - Omset Kasir Data:', [
            'user_id' => $user->id,
            'branch_id' => $selectedBranchId,
            'total_transactions' => $jumlahTransaksi,
            'omset_kasir' => $omsetKasir,
            'omset_bpjs' => $omsetBpjs,
            'omset_umum' => $omsetUmum,
            'bpjs_transactions_count' => $bpjsTransactions->count(),
        ]);
            
        $omsetUmum = Penjualan::where('branch_id', $selectedBranchId)
            ->where('user_id', $user->id)
            ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
            ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
            ->whereHas('pasien', function($q) {
                $q->where('service_type', 'UMUM')
                  ->orWhereNotIn('service_type', ['BPJS I', 'BPJS II', 'BPJS III']);
            })->sum('total');
            
        $jumlahTransaksi = Penjualan::where('branch_id', $selectedBranchId)
            ->where('user_id', $user->id)
            ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
            ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
            ->count();
            
        $transaksiTerbaru = Penjualan::where('branch_id', $selectedBranchId)
            ->where('user_id', $user->id)
            ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
            ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
            ->with('pasien')
            ->latest()
            ->take(5)
            ->get();
        
        return [
            'omset_kasir' => $omsetKasir,
            'omset_bpjs' => $omsetBpjs,
            'omset_umum' => $omsetUmum,
            'jumlah_transaksi' => $jumlahTransaksi,
            'transaksi_terbaru' => $transaksiTerbaru->map(function($t) {
                return [
                    'id' => $t->id,
                    'no_transaksi' => $t->kode_penjualan,
                    'nama_pasien' => $t->pasien->nama_pasien ?? '-',
                    'service_type' => $t->pasien->service_type ?? 'UMUM',
                    'total' => $t->total,
                    'status' => $t->status_pengerjaan,
                    'tanggal' => $t->created_at->format('d/m/Y'),
                ];
            }),
        ];
    }
    
    private function getAdminData($user, $selectedBranchId, $omsetStart, $omsetEnd)
    {
        $totalTransaksiHariIni = Penjualan::when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
            ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
            ->count();
            
        // Hitung total omset hari ini dengan harga default BPJS
        $allTransactions = Penjualan::when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
            ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
            ->with('pasien')
            ->get();
            
        $totalOmsetHariIni = $allTransactions->sum(function($transaksi) {
            $serviceType = $transaksi->pasien->service_type ?? 'UMUM';
            
            // Untuk transaksi BPJS, gunakan harga default
            if (in_array($serviceType, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                switch ($serviceType) {
                    case 'BPJS I':
                        return BpjsPricingService::BPJS_I_PRICE;
                    case 'BPJS II':
                        return BpjsPricingService::BPJS_II_PRICE;
                    case 'BPJS III':
                        return BpjsPricingService::BPJS_III_PRICE;
                }
            }
            
            // Untuk transaksi non-BPJS, gunakan total asli
            return $transaksi->total;
        });
            
        // Hitung rekap omset kasir dengan harga default BPJS
        $rekapTransactions = Penjualan::when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
            ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
            ->with(['user', 'pasien'])
            ->get();
            
        $rekapOmsetKasir = $rekapTransactions->groupBy('user_id')->map(function($transactions) {
            $firstTransaction = $transactions->first();
            $totalOmset = $transactions->sum(function($transaksi) {
                $serviceType = $transaksi->pasien->service_type ?? 'UMUM';
                
                // Untuk transaksi BPJS, gunakan harga default
                if (in_array($serviceType, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                    switch ($serviceType) {
                        case 'BPJS I':
                            return BpjsPricingService::BPJS_I_PRICE;
                        case 'BPJS II':
                            return BpjsPricingService::BPJS_II_PRICE;
                        case 'BPJS III':
                            return BpjsPricingService::BPJS_III_PRICE;
                    }
                }
                
                // Untuk transaksi non-BPJS, gunakan total asli
                return $transaksi->total;
            });
            
            return (object) [
                'user_id' => $firstTransaction->user_id,
                'user' => $firstTransaction->user,
                'total_omset' => $totalOmset,
                'jumlah_transaksi' => $transactions->count()
            ];
        })->values();
        
        return [
            'total_transaksi_hari_ini' => $totalTransaksiHariIni,
            'total_omset_hari_ini' => $totalOmsetHariIni,
            'rekap_omset_kasir' => $rekapOmsetKasir->map(function($r) {
                return [
                    'kasir_name' => $r->user->name ?? 'Unknown',
                    'total_omset' => $r->total_omset,
                    'jumlah_transaksi' => $r->jumlah_transaksi,
                ];
            }),
        ];
    }
    
    public function notifications(Request $request)
    {
        $user = auth()->user();
        
        return response()->stream(function () use ($user) {
            $lastNotificationCheck = now()->subMinutes(5);
            
            while (true) {
                $notifications = $this->getNotifications($user, $lastNotificationCheck);
                
                foreach ($notifications as $notification) {
                    echo "data: " . json_encode($notification) . "\n\n";
                }
                
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
                
                $lastNotificationCheck = now();
                sleep(10); // Check for notifications every 10 seconds
                
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
    
    public function stockUpdates(Request $request)
    {
        $user = auth()->user();
        
        return response()->stream(function () use ($user) {
            $lastStockCheck = now()->subMinutes(1);
            
            while (true) {
                $stockUpdates = $this->getStockUpdates($user, $lastStockCheck);
                
                if (!empty($stockUpdates)) {
                    echo "data: " . json_encode($stockUpdates) . "\n\n";
                }
                
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
                
                $lastStockCheck = now();
                sleep(5); // Check stock updates every 5 seconds
                
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
    
    private function getNotifications($user, $since)
    {
        $notifications = [];
        
        // Check for new transactions
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $newTransactions = Penjualan::where('created_at', '>', $since)
                ->when(!$user->isSuperAdmin(), fn($q) => $q->where('branch_id', $user->branch_id))
                ->with('pasien', 'user')
                ->get();
                
            foreach ($newTransactions as $transaction) {
                $notifications[] = [
                    'type' => 'new_transaction',
                    'title' => 'Transaksi Baru',
                    'message' => "Transaksi {$transaction->no_transaksi} oleh {$transaction->user->name}",
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'no_transaksi' => $transaction->no_transaksi,
                        'total' => $transaction->total,
                        'kasir' => $transaction->user->name,
                    ],
                    'timestamp' => $transaction->created_at->toISOString(),
                ];
            }
        }
        
        // Check for transactions ready for pickup
        $readyTransactions = Penjualan::where('status_pengerjaan', 'Siap Diambil')
            ->where('updated_at', '>', $since)
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('branch_id', $user->branch_id))
            ->with('pasien')
            ->get();
            
        foreach ($readyTransactions as $transaction) {
            $notifications[] = [
                'type' => 'ready_for_pickup',
                'title' => 'Siap Diambil',
                'message' => "Pesanan {$transaction->pasien->nama} siap diambil",
                'data' => [
                    'transaction_id' => $transaction->id,
                    'no_transaksi' => $transaction->no_transaksi,
                    'pasien_nama' => $transaction->pasien->nama,
                ],
                'timestamp' => $transaction->updated_at->toISOString(),
            ];
        }
        
        return $notifications;
    }
    
    private function getStockUpdates($user, $since)
    {
        $updates = [];
        $selectedBranchId = $user->isSuperAdmin() ? null : $user->branch_id;
        
        // Check for frame stock updates
        $frameUpdates = \App\Models\Frame::when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->where('updated_at', '>', $since)
            ->get()
            ->map(function($frame) {
                return [
                    'type' => 'frame_stock_update',
                    'product_type' => 'Frame',
                    'product_id' => $frame->id,
                    'product_name' => $frame->merk_frame . ' - ' . $frame->jenis_frame,
                    'new_stock' => $frame->stok,
                    'kode' => $frame->kode_frame,
                    'branch_name' => $frame->branch?->name ?? '-',
                    'updated_at' => $frame->updated_at->toISOString(),
                    'alert_level' => $frame->stok <= 5 ? 'low' : ($frame->stok <= 10 ? 'medium' : 'normal')
                ];
            });
            
        // Check for lensa stock updates
        $lensaUpdates = \App\Models\Lensa::when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->where('updated_at', '>', $since)
            ->get()
            ->map(function($lensa) {
                return [
                    'type' => 'lensa_stock_update',
                    'product_type' => 'Lensa',
                    'product_id' => $lensa->id,
                    'product_name' => $lensa->merk_lensa . ' - ' . $lensa->type,
                    'new_stock' => $lensa->stok,
                    'kode' => $lensa->kode_lensa,
                    'branch_name' => $lensa->branch?->name ?? '-',
                    'updated_at' => $lensa->updated_at->toISOString(),
                    'alert_level' => $lensa->stok <= 5 ? 'low' : ($lensa->stok <= 10 ? 'medium' : 'normal')
                ];
            });
            
        // Check for aksesoris stock updates
        $aksesorisUpdates = \App\Models\Aksesoris::when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->where('updated_at', '>', $since)
            ->get()
            ->map(function($aksesoris) {
                return [
                    'type' => 'aksesoris_stock_update',
                    'product_type' => 'Aksesoris',
                    'product_id' => $aksesoris->id,
                    'product_name' => $aksesoris->nama_produk,
                    'new_stock' => $aksesoris->stok,
                    'kode' => 'AKS-' . str_pad($aksesoris->id, 6, '0', STR_PAD_LEFT),
                    'branch_name' => $aksesoris->branch?->name ?? '-',
                    'updated_at' => $aksesoris->updated_at->toISOString(),
                    'alert_level' => $aksesoris->stok <= 5 ? 'low' : ($aksesoris->stok <= 10 ? 'medium' : 'normal')
                ];
            });
        
        // Combine all updates
        $allUpdates = $frameUpdates->concat($lensaUpdates)->concat($aksesorisUpdates);
        
        // Return summary if there are updates
        if ($allUpdates->isNotEmpty()) {
            return [
                'timestamp' => now()->toISOString(),
                'total_updates' => $allUpdates->count(),
                'low_stock_alerts' => $allUpdates->where('alert_level', 'low')->count(),
                'medium_stock_alerts' => $allUpdates->where('alert_level', 'medium')->count(),
                'updates' => $allUpdates->toArray(),
                'summary' => [
                    'frames_updated' => $frameUpdates->count(),
                    'lensas_updated' => $lensaUpdates->count(),
                    'aksesoris_updated' => $aksesorisUpdates->count(),
                ]
            ];
        }
        
        return [];
    }
}