<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Myth extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'myth_strength' => 'float',
        'created_tick' => 'integer',
        'current_version_id' => 'string',
    ];

    public function versions()
    {
        return $this->hasMany(MythVersion::class);
    }

    public function currentVersion()
    {
        return $this->belongsTo(MythVersion::class, 'current_version_id');
    }

    public function scar()
    {
        return $this->belongsTo(Scar::class, 'linked_scar_id');
    }
}
