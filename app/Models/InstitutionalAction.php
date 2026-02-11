<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InstitutionalAction extends Model
{
    use HasUuids;

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
