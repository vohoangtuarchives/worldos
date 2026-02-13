<?php

namespace App\Domains\Vietnamese\Services;

use App\Models\World;
use App\Domains\Vietnamese\Models\RealmContact;
use Illuminate\Support\Collection;

class RealmContactService
{
    /**
     * Get all active realm contacts for a specific era
     */
    public function getActiveContacts(int $era): Collection
    {
        return RealmContact::where('start_era', '<=', $era)
            ->where(function ($query) use ($era) {
                $query->whereNull('end_era')
                      ->orWhere('end_era', '>=', $era);
            })
            ->get();
    }

    /**
     * Calculate total influence modifiers from external realms
     */
    public function calculateRealmInfluence(World $world): array
    {
        $currentEra = (int) floor(($world->current_time ?? 0) / 50);
        $contacts = $this->getActiveContacts($currentEra);

        $modifiers = [
            'military_pressure' => 0.0,
            'cultural_assimilation' => 0.0,
            'trade_bonus' => 0.0,
            'instability' => 0.0,
        ];

        foreach ($contacts as $contact) {
            $intensity = $contact->intensity;

            switch ($contact->influence_type) {
                case 'DOMINATION':
                    // High military pressure, cultural assimilation, instability
                    $modifiers['military_pressure'] += $intensity * 0.8;
                    $modifiers['cultural_assimilation'] += $intensity * 0.5;
                    $modifiers['instability'] += $intensity * 0.3;
                    break;

                case 'TRADE':
                    // Economic bonus, mild cultural exchange
                    $modifiers['trade_bonus'] += $intensity * 0.6;
                    $modifiers['cultural_assimilation'] += $intensity * 0.1;
                    break;
                
                case 'WAR':
                    // High pressure and instability
                    $modifiers['military_pressure'] += $intensity * 1.0;
                    $modifiers['instability'] += $intensity * 0.5;
                    break;

                case 'ALLIANCE':
                    // Stability and trade
                    $modifiers['instability'] -= $intensity * 0.2;
                    $modifiers['trade_bonus'] += $intensity * 0.3;
                    break;
            }
        }

        return $modifiers;
    }
}
