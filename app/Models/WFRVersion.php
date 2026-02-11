<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WFRVersion extends Model
{
    protected $table = 'wfr_versions';

    protected $fillable = [
        'version',
        'changelog',
        'released_at',
        'is_stable',
    ];

    protected $casts = [
        'released_at' => 'datetime',
    ];

    /**
     * Get the latest stable version
     */
    public static function latestStable(): ?string
    {
        return static::where('is_stable', true)
            ->orderByDesc('released_at')
            ->value('version');
    }
}
