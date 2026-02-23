<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * WorldOS V5 Chronicle Event Eloquent Model.
 */
class ChronicleEventModel extends Model
{
    use HasUuids;

    protected $table = 'chronicle_events';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'universe_id',
        'tick',
        'seed',
        'type',
        'title',
        'severity',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'tick'        => 'integer',
        'seed'        => 'integer',
        'occurred_at' => 'datetime',
    ];

    public function universe()
    {
        return $this->belongsTo(UniverseModel::class, 'universe_id');
    }
}
