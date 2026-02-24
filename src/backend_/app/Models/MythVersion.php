<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MythVersion extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'ideology_pull_vector' => 'array',
        'created_tick' => 'integer',
        'version_number' => 'integer',
    ];

    public function myth()
    {
        return $this->belongsTo(Myth::class);
    }
}
