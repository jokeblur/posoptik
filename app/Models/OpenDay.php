<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenDay extends Model
{
    protected $fillable = ['branch_id', 'tanggal', 'is_open'];
    public function branch() {
        return $this->belongsTo(Branch::class);
    }
} 