<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MetaImpulse extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'type',
        'payload',
        'strength',
        'decay_rate',
        'created_at_tick',
        'active_until_tick',
    ];

    protected $casts = [
        'payload' => 'array',
        'strength' => 'float',
        'decay_rate' => 'float',
        'created_at_tick' => 'integer',
        'active_until_tick' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
