<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionalAction extends Model
{

    protected $guarded = [];

    protected $casts = [
        'target_params' => 'array',
        'resource_cost' => 'float',
        'tick' => 'integer',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
