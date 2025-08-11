<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transfer',
        'from_branch_id',
        'to_branch_id',
        'requested_by',
        'approved_by',
        'status',
        'notes',
        'rejection_reason',
        'approved_at',
        'completed_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the source branch
     */
    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * Get the destination branch
     */
    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Get the user who requested the transfer
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the transfer
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the transfer details
     */
    public function details()
    {
        return $this->hasMany(StockTransferDetail::class);
    }

    /**
     * Scope to get pending transfers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope to get approved transfers
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    /**
     * Scope to get transfers by user's accessible branches
     */
    public function scopeAccessibleByUser($query, $user)
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $query;
        }
        
        return $query->where(function($q) use ($user) {
            $q->where('from_branch_id', $user->branch_id)
              ->orWhere('to_branch_id', $user->branch_id);
        });
    }

    /**
     * Check if transfer can be approved by user
     */
    public function canBeApprovedBy($user)
    {
        return ($user->isSuperAdmin() || $user->isAdmin()) && $this->status === 'Pending';
    }

    /**
     * Check if transfer can be completed by user
     */
    public function canBeCompletedBy($user)
    {
        return $this->status === 'Approved' && 
               ($user->isSuperAdmin() || $user->isAdmin() || 
                $user->branch_id === $this->to_branch_id);
    }

    /**
     * Approve the transfer
     */
    public function approve($approvedBy)
    {
        $this->update([
            'status' => 'Approved',
            'approved_by' => $approvedBy->id,
            'approved_at' => now()
        ]);
    }

    /**
     * Reject the transfer
     */
    public function reject($rejectedBy, $reason)
    {
        $this->update([
            'status' => 'Rejected',
            'approved_by' => $rejectedBy->id,
            'rejection_reason' => $reason,
            'approved_at' => now()
        ]);
    }

    /**
     * Complete the transfer
     */
    public function complete()
    {
        DB::transaction(function () {
            // Update stock in both branches
            foreach ($this->details as $detail) {
                $item = $detail->itemable;
                
                // Reduce stock from source branch
                if ($item->branch_id === $this->from_branch_id) {
                    $item->decrement('stok', $detail->quantity);
                }
                
                // Add stock to destination branch
                if ($item->branch_id === $this->to_branch_id) {
                    $item->increment('stok', $detail->quantity);
                } else {
                    // Create new item in destination branch if it doesn't exist
                    $newItem = $item->replicate();
                    $newItem->branch_id = $this->to_branch_id;
                    $newItem->stok = $detail->quantity;
                    $newItem->created_at = now();
                    $newItem->updated_at = now();
                    $newItem->save();
                }
            }
            
            $this->update([
                'status' => 'Completed',
                'completed_at' => now()
            ]);
        });
    }

    /**
     * Generate transfer code
     */
    public static function generateCode()
    {
        $lastTransfer = self::latest()->first();
        $id = $lastTransfer ? $lastTransfer->id + 1 : 1;
        return 'TRF' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get total value of the transfer
     */
    public function getTotalValue()
    {
        return $this->details->sum('total_price');
    }

    /**
     * Get total quantity of items
     */
    public function getTotalQuantity()
    {
        return $this->details->sum('quantity');
    }

    /**
     * Check if transfer can be cancelled
     */
    public function canBeCancelled()
    {
        return $this->status === 'Pending';
    }

    /**
     * Get status color for display
     */
    public function getStatusColor()
    {
        $colors = [
            'Pending' => 'warning',
            'Approved' => 'success',
            'Rejected' => 'danger',
            'Completed' => 'info',
            'Cancelled' => 'default'
        ];

        return $colors[$this->status] ?? 'default';
    }

    /**
     * Get status text in Indonesian
     */
    public function getStatusText()
    {
        $texts = [
            'Pending' => 'Menunggu Persetujuan',
            'Approved' => 'Disetujui',
            'Rejected' => 'Ditolak',
            'Completed' => 'Selesai',
            'Cancelled' => 'Dibatalkan'
        ];

        return $texts[$this->status] ?? $this->status;
    }

    /**
     * Get pending transfers count for notifications
     */
    public static function getPendingCount($user = null)
    {
        $query = self::where('status', 'Pending');
        
        if ($user && !$user->canAccessAllBranches()) {
            $query->where('from_branch_id', $user->branch_id);
        }
        
        return $query->count();
    }

    /**
     * Get transfers that need user attention
     */
    public static function getAttentionNeeded($user)
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            // Admins see all pending transfers
            return self::where('status', 'Pending')->with(['fromBranch', 'toBranch'])->latest()->take(5)->get();
        } elseif ($user->isKasir()) {
            // Kasir see their own pending transfers
            return self::where('status', 'Pending')
                ->where('requested_by', $user->id)
                ->with(['fromBranch', 'toBranch'])
                ->latest()
                ->take(5)
                ->get();
        }
        
        return collect();
    }
}
