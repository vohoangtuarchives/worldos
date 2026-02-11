<?php

namespace App\Models\World;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldStateTransition extends Model
{
    use HasUuids;

    protected $table = 'world_state_transitions';

    protected $fillable = [
        'from_state_id',
        'to_preset_id',
        'transition_policy',
        'reason',
    ];

    public $timestamps = false;
    
    // Only created_at in migration
    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }
    
    public function setUpdatedAt($value) {}
    public function getUpdatedAtColumn() { return null; }

    public function fromState(): BelongsTo
    {
        return $this->belongsTo(WorldState::class, 'from_state_id');
    }

    public function toPreset(): BelongsTo
    {
        return $this->belongsTo(WorldPreset::class, 'to_preset_id');
    }
}
