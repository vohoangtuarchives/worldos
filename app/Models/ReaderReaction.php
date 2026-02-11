<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderReaction extends Model
{
    protected $fillable = [
        'session_id',
        'world_id',
        'tick',
        'type',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ReaderSession::class);
    }
}
