<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tuzy\Domain\Cosmology\Entity\World;

class WorldChronicleEvent extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'world_chronicle_events';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'world_id',
        'year',
        'type',
        'title',
        'description',
        'severity',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'year'     => 'integer',
    ];
}
