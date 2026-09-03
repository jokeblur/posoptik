<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisplayMedia extends Model
{
    protected $table = 'display_media';

    protected $fillable = ['judul', 'tipe', 'path', 'is_active', 'urutan'];

    protected $casts = ['is_active' => 'boolean'];
}