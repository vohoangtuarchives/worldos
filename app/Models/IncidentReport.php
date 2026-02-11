<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'world_id',
        'status',
        'severity',
        'summary',
        'impact_assessment',
        'timeline_events',
        'root_cause',
        'five_whys',
        'resolution_justification',
        'action_items'
    ];

    protected $casts = [
        'impact_assessment' => 'array',
        'timeline_events' => 'array',
        'five_whys' => 'array',
        'action_items' => 'array',
    ];

    public function world()
    {
        return $this->belongsTo(World::class);
    }
}
