<?php

namespace Tuzy\Domain\Vietnamese\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RealmContact extends Model
{
    use HasUuids;

    protected $table = 'realm_contacts';

    protected $fillable = [
        'realm_name', // e.g. "China", "France", "Champa"
        'period_name', // e.g. "Bac Thuoc 1", "French Indochina"
        'start_era',
        'end_era',
        'influence_type', // e.g. "DOMINATION", "TRADE", "WAR"
        'intensity', // 0.0 to 1.0 (Impact on culture)
        'description',
        'metadata' // JSON for specific artifacts/effects
    ];

    protected $casts = [
        'start_era' => 'integer',
        'end_era' => 'integer',
        'intensity' => 'float',
        'metadata' => 'array',
    ];

    /**
     * Check if this contact is active in a given era
     */
    public function isActiveIn(int $era): bool
    {
        return $era >= $this->start_era && ($this->end_era === null || $era <= $this->end_era);
    }
}
