<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * WorldOS V5 Universe Snapshot Eloquent Model.
 * Stores one complete state capture per tick for deterministic replay.
 */
class UniverseSnapshot extends Model
{
    use HasUuids;

    protected $table = 'universe_snapshots';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'universe_id',
        'tick',
        'seed',
        'entropy',
        'stability_index',
        'existence_weight',
        'state_vector',
        'captured_at',
    ];

    protected $casts = [
        'state_vector'     => 'array',
        'tick'             => 'integer',
        'seed'             => 'integer',
        'entropy'          => 'float',
        'stability_index'  => 'float',
        'existence_weight' => 'float',
        'captured_at'      => 'datetime',
    ];

    public function universe()
    {
        return $this->belongsTo(UniverseModel::class, 'universe_id');
    }
}
