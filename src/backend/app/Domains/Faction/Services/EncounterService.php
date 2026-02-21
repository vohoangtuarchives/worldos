<?php

namespace App\Domains\Faction\Services;

use App\Models\Faction;
use App\Models\World;
use App\Domains\World\Services\WorldEventLedger;
use Tuzy\Domain\Faction\ValueObject\Leader;

class EncounterService
{
    protected WorldEventLedger $ledger;

    // Probability of an encounter per tick (e.g., 0.05 = 5%)
    const ENCOUNTER_CHANCE = 0.05;

    public function __construct(WorldEventLedger $ledger)
    {
        $this->ledger = $ledger;
    }

    /**
     * Check and process potential encounter for a faction leader
     */
    public function checkEncounter(Faction $faction): void
    {
        // Simple RNG check
        if (mt_rand(0, 100) / 100.0 > self::ENCOUNTER_CHANCE) {
            return;
        }

        $leader = $faction->getLeader();
        $world = $faction->world;
        
        // Determine Event Type
        $eventType = $this->determineEventType($leader);
        
        // Process Event Effect
        $result = $this->processEventEffect($eventType, $leader, $faction);
        
        // Save Updated Leader State
        $faction->updateLeader($leader);
        $faction->save();

        // Record to Ledger
        $this->ledger->record(
            $world,
            'personal_event',
            $result['description'],
            0.1, // Personal events have low global magnitude
            0.5, // But moderate permanence for the character
            'Public',
            [
                'actor_id' => $faction->id,
                'actor_name' => $leader->name,
                'subtype' => $eventType,
                'details' => $result['details']
            ]
        );
    }

    private function determineEventType(Leader $leader): string
    {
        $types = ['epiphany', 'encounter', 'tragedy', 'discovery', 'bond_formed'];
        return $types[array_rand($types)];
    }

    private function processEventEffect(string $type, Leader $leader, Faction $faction): array
    {
        $desc = "";
        $details = [];

        switch ($type) {
            case 'epiphany':
                $desc = "{$leader->name} đột nhiên ngộ ra chân lý võ học trong lúc thiền định.";
                $leader->personality->wisdom += 0.05; 
                $leader->quirks[] = "Thích ngắm sao"; 
                $details = ['gain_stat' => 'wisdom'];
                break;
                
            case 'encounter':
                $itemName = $this->generateArtifactName();
                $desc = "{$leader->name} tình cờ tìm thấy {$itemName} trong một hang động bí ẩn.";
                $leader->inventory[] = ['name' => $itemName, 'power' => mt_rand(1, 10)];
                $details = ['gain_item' => $itemName];
                break;

            case 'tragedy':
                $desc = "{$leader->name} nhận tin dữ về người thân qua đời, tâm tính trở nên trầm mặc.";
                $leader->quirks[] = "U sầu";
                $details = ['mood_change' => 'sad'];
                break;

            case 'discovery':
                $desc = "{$leader->name} phát hiện một bí mật khủng khiếp về lịch sử môn phái.";
                $leader->conversations[] = "Phát hiện bí mật tại tick " . ($faction->world->tick ?? 0);
                $details = ['gain_knowledge' => true];
                break;

            case 'bond_formed':
                // Find a target faction
                $target = Faction::where('world_id', $faction->world_id)
                    ->where('id', '!=', $faction->id)
                    ->inRandomOrder()
                    ->first();

                if ($target) {
                    $targetLeader = $target->getLeader();
                    $relationType = (mt_rand(0, 1) === 0) ? 'FRIEND' : 'RIVAL';
                    $desc = ($relationType === 'FRIEND')
                        ? "{$leader->name} tình cờ gặp {$targetLeader->name} và kết giao bằng hữu."
                        : "{$leader->name} xảy ra xích mích với {$targetLeader->name}, gieo mầm hận thù.";
                    
                    $leader->relationships[$target->id] = ['type' => $relationType, 'affinity' => ($relationType === 'FRIEND' ? 0.5 : -0.5)];
                    $details = ['relationship' => $relationType, 'target' => $targetLeader->name];
                } else {
                    $desc = "{$leader->name} cảm thấy cô đơn giữa thế gian rộng lớn.";
                    $leader->quirks[] = "Cô độc";
                }
                break;
        }

        return ['description' => $desc, 'details' => $details];
    }

    private function generateArtifactName(): string
    {
        $adj = ['Huyết', 'Thanh', 'Huyền', 'Tử', 'Thiên'];
        $noun = ['Kiếm', 'Đao', 'Ngọc', 'Đỉnh', 'Châu'];
        return $adj[array_rand($adj)] . " " . $noun[array_rand($noun)];
    }
}
