<?php

declare(strict_types=1);

namespace App\Domains\World\Services;

use App\Domains\World\Aggregates\WorldAggregate;
use App\Domains\World\ValueObjects\EntropyScore;
use App\Domains\World\Events\ShockEvent;
use Illuminate\Support\Facades\Config;

final class ShockEventGenerator
{
    private array $eventWeights;

    public function __construct()
    {
        $this->eventWeights = Config::get('world.shock_event_weights', [
            'plague' => 0.15,
            'civil_war' => 0.20,
            'magic_collapse' => 0.10,
            'famine' => 0.15,
            'invasion' => 0.15,
            'earthquake' => 0.10,
            'myth_awakening' => 0.15,
        ]);
    }

    public function generate(
        WorldAggregate $world,
        EntropyScore $entropy,
        int $tick
    ): ShockEvent {
        
        $eventType = $this->selectEventType($entropy, $world);
        $severity = $this->calculateSeverity($entropy, $world);
        $region = $this->selectAffectedRegion($world);

        return $this->createEvent($eventType, $severity, $region, $entropy);
    }

    private function selectEventType(EntropyScore $entropy, WorldAggregate $world): string
    {
        // Adjust weights based on entropy and world state
        $adjustedWeights = $this->adjustWeightsByEntropy($this->eventWeights, $entropy);
        $adjustedWeights = $this->adjustWeightsByWorldState($adjustedWeights, $world);

        return $this->weightedRandomChoice($adjustedWeights);
    }

    private function adjustWeightsByEntropy(array $weights, EntropyScore $entropy): array
    {
        $entropyLevel = $entropy->value();
        $adjusted = $weights;

        if ($entropyLevel > 0.7) {
            // High entropy: more catastrophic events
            $adjusted['civil_war'] *= 1.5;
            $adjusted['magic_collapse'] *= 2.0;
            $adjusted['myth_awakening'] *= 1.8;
        } elseif ($entropyLevel > 0.4) {
            // Medium entropy: balanced events
            $adjusted['famine'] *= 1.3;
            $adjusted['invasion'] *= 1.2;
        } else {
            // Low entropy: mostly natural disasters
            $adjusted['earthquake'] *= 2.0;
            $adjusted['plague'] *= 1.5;
        }

        return $this->normalizeWeights($adjusted);
    }

    private function adjustWeightsByWorldState(array $weights, WorldAggregate $world): array
    {
        $adjusted = $weights;

        // Adjust based on faction instability
        if ($world->factionInstability() > 0.6) {
            $adjusted['civil_war'] *= 2.0;
        }

        // Adjust based on resource scarcity
        if ($world->resourceScarcity() > 0.7) {
            $adjusted['famine'] *= 1.8;
            $adjusted['invasion'] *= 1.5;
        }

        // Adjust based on myth instability
        if ($world->mythInstability() > 0.6) {
            $adjusted['magic_collapse'] *= 2.0;
            $adjusted['myth_awakening'] *= 1.5;
        }

        return $this->normalizeWeights($adjusted);
    }

    private function calculateSeverity(EntropyScore $entropy, WorldAggregate $world): float
    {
        $baseSeverity = 0.3 + ($entropy->value() * 0.5); // 0.3 to 0.8 base

        // Increase severity for unstable worlds
        $instabilityModifier = ($world->factionInstability() + $world->resourceScarcity()) * 0.1;

        $finalSeverity = $baseSeverity + $instabilityModifier;

        return min(1.0, max(0.1, $finalSeverity));
    }

    private function selectAffectedRegion(WorldAggregate $world): string
    {
        $regions = $world->getRegions();
        
        if (empty($regions)) {
            return 'world_center';
        }

        // Prefer regions with higher instability
        $weights = [];
        foreach ($regions as $region) {
            $instability = $world->getRegionInstability($region);
            $weights[$region] = 1.0 + $instability;
        }

        return $this->weightedRandomChoice($weights);
    }

    private function createEvent(string $type, float $severity, string $region, EntropyScore $entropy): ShockEvent
    {
        return match ($type) {
            'plague' => ShockEvent::plague($severity, $region),
            'civil_war' => ShockEvent::civilWar($severity, $region),
            'magic_collapse' => ShockEvent::magicCollapse($severity, $region),
            'famine' => ShockEvent::famine($severity, $region),
            'invasion' => ShockEvent::invasion($severity, $region),
            'earthquake' => ShockEvent::earthquake($severity, $region),
            'myth_awakening' => ShockEvent::mythAwakening($severity, $region),
            default => throw new \InvalidArgumentException("Unknown shock event type: {$type}"),
        };
    }

    private function weightedRandomChoice(array $weights): string
    {
        $total = array_sum($weights);
        $rand = (mt_rand() / mt_getrandmax()) * $total;

        foreach ($weights as $item => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return $item;
            }
        }

        // Fallback to first item
        return array_key_first($weights);
    }

    private function normalizeWeights(array $weights): array
    {
        $total = array_sum($weights);
        
        if ($total === 0) {
            return $weights;
        }

        return array_map(fn($weight) => $weight / $total, $weights);
    }

    public function generateBatch(
        WorldAggregate $world,
        EntropyScore $entropy,
        int $tick,
        int $count
    ): array {
        
        $events = [];
        
        for ($i = 0; $i < $count; $i++) {
            $events[] = $this->generate($world, $entropy, $tick);
        }

        return $events;
    }

    public function predictNextEvents(
        WorldAggregate $world,
        EntropyScore $entropy,
        int $futureTicks = 5
    ): array {
        
        $predictions = [];
        $currentEntropy = $entropy;

        for ($i = 1; $i <= $futureTicks; $i++) {
            // Simulate entropy increase
            $entropyIncrease = 0.02 * (1 + $currentEntropy->value());
            $currentEntropy = new EntropyScore(min(1.0, $currentEntropy->value() + $entropyIncrease));

            $eventType = $this->selectEventType($currentEntropy, $world);
            $severity = $this->calculateSeverity($currentEntropy, $world);
            $probability = $this->calculateEventProbability($currentEntropy);

            $predictions[] = [
                'tick' => $world->currentTick() + $i,
                'predicted_event' => $eventType,
                'predicted_severity' => $severity,
                'probability' => $probability,
                'predicted_entropy' => $currentEntropy->value(),
            ];
        }

        return $predictions;
    }

    private function calculateEventProbability(EntropyScore $entropy): float
    {
        $baseProbability = 0.1;
        $entropyBonus = $entropy->value() * 0.3;
        
        return min(1.0, $baseProbability + $entropyBonus);
    }
}
