<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SacredArchetype extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'parent_archetype_key',
        'name',
        'sacred_strength',
        'canonized_at_tick',
        'survival_eras',
        'myth_profile',
        'mutation_profile',
        'status',
    ];

    protected $casts = [
        'sacred_strength' => 'float',
        'canonized_at_tick' => 'integer',
        'survival_eras' => 'integer',
        'myth_profile' => 'array',
        'mutation_profile' => 'array',
    ];
}
