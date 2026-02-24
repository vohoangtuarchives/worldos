<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent Model for universe_snapshots table.
 *
 * Infrastructure layer — NO business logic.
 *
 * @property int $id
 * @property string $universe_id
 * @property int $tick
 * @property array $state_vector
 * @property array|null $cascade_state
 * @property float|null $stability_metric
 * @property float $entropy
 * @property array|null $metrics
 * @property \Illuminate\Support\Carbon $created_at
 */
class UniverseSnapshotModel extends Model
{
    protected $table = 'universe_snapshots';

    public $timestamps = false;

    protected $fillable = [
        'universe_id',
        'tick',
        'state_vector',
        'cascade_state',
        'stability_metric',
        'entropy',
        'metrics',
    ];

    protected function casts(): array
    {
        return [
            'tick' => 'integer',
            'state_vector' => 'array',
            'cascade_state' => 'array',
            'stability_metric' => 'float',
            'entropy' => 'float',
            'metrics' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function universe(): BelongsTo
    {
        return $this->belongsTo(UniverseModel::class, 'universe_id');
    }

    /**
     * Boot the model to auto-set created_at.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            $model->created_at = $model->created_at ?? now();
        });
    }
}
