<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    use HasUuids;

    protected $table = 'ai_generations';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'world_id',
        'prompt_hash',
        'system_prompt',
        'user_prompt',
        'response_content',
        'status',
        'violations',
        'attempt_number',
        'context_type', // added later in migration
    ];

    protected $casts = [
        'violations' => 'array',
        'attempt_number' => 'integer',
    ];

    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }
}
