<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObserverVersion extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

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
