<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Outline cấp saga / season / arc. PlotPlannerService tạo; SerialArcPlanner đọc khi có.
 */
class NarrativeArcOutline extends Model
{
    use HasUuids;

    public const LEVEL_SAGA = 'saga';
    public const LEVEL_SEASON = 'season';
    public const LEVEL_ARC = 'arc';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';

    protected $table = 'narrative_arc_outlines';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'narrative_series_id',
        'level',
        'index',
        'title',
        'one_line',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'index' => 'integer',
    ];

    public function narrativeSeries(): BelongsTo
    {
        return $this->belongsTo(NarrativeSeries::class, 'narrative_series_id');
    }
}
