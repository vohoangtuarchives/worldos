<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldPrimitiveBinding extends Model
{
    protected $table = 'world_primitive_bindings';

    protected $fillable = [
        'world_id',
        'world_primitive_id',
        'wfr_version'
    ];

    public function world()
    {
        return $this->belongsTo(World::class);
    }

    public function primitive()
    {
        return $this->belongsTo(WorldPrimitive::class, 'world_primitive_id');
    }
}
