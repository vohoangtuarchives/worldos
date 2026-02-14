<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObserverVersion extends Model
{
    protected $fillable = [
        'observer_id',
        'version',
        'rules',
    ];

    protected $casts = [
        'rules' => 'array',
    ];

    public function observer(): BelongsTo
    {
        return $this->belongsTo(Observer::class);
    }
}
