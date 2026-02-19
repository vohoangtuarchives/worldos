<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIFeatureAgentConfig extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'ai_feature_agent_configs';

    protected $fillable = [
        'feature_key',
        'agent_name',
        'provider',
        'model',
        'system_prompt',
        'options',
        'enabled',
    ];

    protected $casts = [
        'options' => 'array',
        'enabled' => 'boolean',
    ];
}
