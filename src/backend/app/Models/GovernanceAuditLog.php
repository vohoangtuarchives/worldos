<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernanceAuditLog extends Model
{
    protected $fillable = [
        'world_id',
        'action_type',
        'operator',
        'metadata',
        'severity',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    /**
     * Static helper to log governance actions
     */
    public static function logAction(
        ?string $worldId,
        string $actionType,
        array $metadata = [],
        string $severity = 'INFO'
    ): self {
        return self::create([
            'world_id' => $worldId,
            'action_type' => $actionType,
            'operator' => auth()->user()->email ?? 'system',
            'metadata' => $metadata,
            'severity' => $severity,
        ]);
    }
}
