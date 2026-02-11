<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactionHistoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'faction_id',
        'turn',
        'intent_type',
        'reasoning',
        'outcome_score'
    ];

    protected $casts = [
        'reasoning' => 'array',
        'outcome_score' => 'float'
    ];

    public function faction()
    {
        return $this->belongsTo(Faction::class);
    }
}
