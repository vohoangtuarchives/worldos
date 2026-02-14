<?php

namespace App\Domains\Narrative\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MaterialSeed extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'narrative_material_seeds';

    protected $fillable = [
        'type', // power_system, social_structure, twist, environment, etc.
        'name',
        'description',
        'attributes', // JSON
        'compatibility_tags', // JSON
    ];

    protected $casts = [
        'attributes' => 'array',
        'compatibility_tags' => 'array',
    ];
}
