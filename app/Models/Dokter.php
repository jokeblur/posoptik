<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;
    protected $table = 'dokter';
    protected $primaryKey = 'id_dokter';
    protected $fillable = [
        'nama_dokter',
        'alamat',
        'nohp',
        'no_str',
        'branch_id',
    ];
}
