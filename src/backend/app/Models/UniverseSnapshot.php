<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UniverseSnapshot extends Model
{
    use HasUuids;

    protected $table = 'universe_snapshots';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'universe_id',
        'tick',
        'state_vector',
        'entropy',
        'stability_index',
        'metrics',
    ];

    protected $casts = [
        'state_vector' => 'array',
        'metrics' => 'array',
        'tick' => 'integer',
        'entropy' => 'float',
        'stability_index' => 'float',
    ];

    public function universe()
    {
        return $this->belongsTo(UniverseModel::class, 'universe_id');
    }
}
