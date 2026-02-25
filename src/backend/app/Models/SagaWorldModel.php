<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SagaWorldModel extends Model
{
    use HasUuids;
    protected $table = 'saga_worlds';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'saga_id',
        'world_id',
        'universe_id',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function saga(): BelongsTo
    {
        return $this->belongsTo(SagaModel::class, 'saga_id');
    }
}
