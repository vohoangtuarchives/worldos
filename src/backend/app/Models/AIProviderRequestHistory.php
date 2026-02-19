<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIProviderRequestHistory extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'ai_provider_request_histories';

    protected $fillable = [
        'provider',
        'model',
        'endpoint',
        'system_prompt',
        'user_prompt',
        'request_payload',
        'response_payload',
        'http_status',
        'status',
        'error_message',
        'duration_ms',
    ];
}
