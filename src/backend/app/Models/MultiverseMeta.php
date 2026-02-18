<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiverseMeta extends Model
{
    protected $table = 'multiverse_meta';
    
    protected $fillable = [
        'entropy_leak',
        'shield_level',
        'void_zones'
    ];

    protected $casts = [
        'void_zones' => 'array'
    ];
}
