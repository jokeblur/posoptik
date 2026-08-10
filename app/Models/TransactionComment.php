<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionComment extends Model
{
    use HasFactory;

    protected $table = 'transaction_comments';

    protected $fillable = [
        'penjualan_id',
        'user_id',
        'comment',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}