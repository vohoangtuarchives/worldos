<?php

declare(strict_types=1);

namespace WorldOS\Core;

use WorldOS\Core\ValueObject\CivilizationSnapshot;
use WorldOS\Core\ValueObject\LifecycleState;
use WorldOS\Core\ValueObject\SimulationResult;
use WorldOS\Core\ValueObject\SubstrateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * SimulationKernel: The core engine of civilization evolution.
 * Handles ideological drift, cultural mutation, and lifecycle transitions.
 */
class SimulationKernel
{
    /**
     * Executes one tick of evolution for a single civilization.
     */
    public function tick(
        CivilizationSnapshot $current,
        SubstrateVector $substrate
    ): SimulationResult {
        // 1. Ideological Drift
        $newIdeology = $current->ideology->drift(
            $current->physics,
            $current->culture,
            $substrate->mutationIntensity
        );

        // 2. Cultural Mutation
        $newCulture = $current->culture->mutate(
            0.01 * $substrate->mutationIntensity, 
            $substrate->driftSeeds
        );

        // 3. Physics Feedback (Coupling Matrix logic)
        $newPhysics = $this->evolvePhysics($current->physics, $newIdeology, $newCulture, $substrate);

        // 4. Update Lifecycle & Stats
        $newStabilityDuration = $this->calculateStability($current, $newIdeology, $newPhysics);
        $newInfluenceMass = $this->calculateInfluence($current, $newIdeology, $newPhysics, $newCulture);
        $newLifecycle = $this->determineLifecycle(
            $current->lifecycle, 
            $newPhysics, 
            $newIdeology, 
            $newCulture, 
            $newStabilityDuration,
            $newInfluenceMass
        );

        $nextSnapshot = new CivilizationSnapshot(
            $current->id,
            $newPhysics,
            $newIdeology,
            $newCulture,
            $newLifecycle,
            $newStabilityDuration,
            $newInfluenceMass
        );

        return new SimulationResult($nextSnapshot, $this->detectEvents($current, $nextSnapshot));
    }

    private function evolvePhysics(
        StateVector $physics, 
        \WorldOS\Society\Faction\ValueObject\IdeologyVector $ideology,
        \WorldOS\Society\Culture\ValueObject\CulturalVector $culture,
        SubstrateVector $substrate
    ): StateVector {
        // Entropy Drift = Militarism * (1.1 - Aesthetic Density)
        $militarism = $ideology->militarism;
        $aesthetic = $culture->aestheticDensity;
        $curiosity = $culture->intellectualCuriosity;
        
        // Order Creation (Siphoning): high curiosity + high aesthetic can siphon entropy
        $siphonEffect = ($curiosity * $aesthetic) * 0.015;
        
        // Disorder Creation (Drift)
        $driftEffect = ($militarism * (1.1 - $aesthetic)) * 0.01;
        
        $substrateFactor = (1.0 / $substrate->entropyDissipation);
        $entropyDelta = ($driftEffect - $siphonEffect) * $substrateFactor * $substrate->mutationIntensity;
        
        $currentEntropy = $physics->get(StateVector::DIMENSION_ENTROPY);
        $newEntropy = max(0.0, min(1.0, $currentEntropy + $entropyDelta));

        return $physics->withDimension(StateVector::DIMENSION_ENTROPY, $newEntropy)
                       ->withDimension(StateVector::DIMENSION_STABILITY, 1.0 - $newEntropy);
    }

    private function calculateStability(CivilizationSnapshot $current, $ideology, $physics): int
    {
        $entropy = $physics->get(StateVector::DIMENSION_ENTROPY);
        
        if ($entropy < 0.4) {
            return $current->stabilityDuration + 1;
        } elseif ($entropy > 0.7) {
             return max(-1000, $current->stabilityDuration - 2); 
        }
        
        return $current->stabilityDuration;
    }

    private function calculateInfluence(CivilizationSnapshot $current, $ideology, $physics, $culture): float
    {
        // Influence = Expansionism * Curiosity * Collectivism
        $growth = ($ideology->expansionism * $culture->intellectualCuriosity * $ideology->collectivism) * 0.05;
        $decay = $physics->get(StateVector::DIMENSION_ENTROPY) * 0.02;
        
        return max(0.1, $current->influenceMass + $growth - $decay);
    }

    private function determineLifecycle(
        LifecycleState $current,
        StateVector $physics,
        $ideology,
        $culture,
        int $stabilityDuration,
        float $influence
    ): LifecycleState {
        // 1. Rebirth Logic: Emergence from the Ashes
        if ($current === LifecycleState::Dormant) {
            // High Intellectual Curiosity in the ruins can spark a new Era
            return ($culture->intellectualCuriosity > 0.8) ? LifecycleState::Emerging : LifecycleState::Dormant;
        }

        // 2. Collapse Case: Deep Chaos -> Dormancy
        if ($physics->get(StateVector::DIMENSION_ENTROPY) > 0.95 && $stabilityDuration < -100) {
            return LifecycleState::Dormant;
        }

        // 3. Normal progression
        if ($influence > 100.0 && $stabilityDuration > 1000) {
            return LifecycleState::Ascended;
        }

        if ($influence > 20.0) {
            return LifecycleState::Dominant;
        }

        if ($influence > 2.0) {
            return LifecycleState::Stable;
        }

        return LifecycleState::Emerging;
    }

    private function detectEvents(CivilizationSnapshot $old, CivilizationSnapshot $new): array
    {
        $events = [];
        
        // 1. Lifecycle Transitions
        if ($old->lifecycle !== $new->lifecycle) {
            $events[] = [
                'type' => 'lifecycle_transition',
                'title' => sprintf('Nền văn minh chuyển sang giai đoạn %s', $this->translateLifecycle($new->lifecycle)),
                'severity' => ($new->lifecycle === LifecycleState::Dormant) ? 'critical' : 'medium',
                'payload' => [
                    'from' => $old->lifecycle->value,
                    'to' => $new->lifecycle->value,
                ],
            ];
        }

        // 2. Ideological Shift: Extreme Thresholds
        $dimensions = ['militarism', 'spiritualism', 'expansionism', 'collectivism', 'purity'];
        foreach ($dimensions as $dim) {
            if ($old->ideology->$dim < 0.8 && $new->ideology->$dim >= 0.8) {
                $events[] = [
                    'type' => 'ideology_shift',
                    'title' => sprintf('Tư tưởng %s đạt ngưỡng cực đoan (%.0f%%)', $dim, $new->ideology->$dim * 100),
                    'severity' => 'high',
                    'payload' => ['dimension' => $dim, 'value' => $new->ideology->$dim],
                ];
            }
        }

        // 3. Rebirth: Rise from Dormancy
        if ($old->lifecycle === LifecycleState::Dormant && $new->lifecycle === LifecycleState::Emerging) {
             $events[] = [
                'type' => 'cosmic_rebirth',
                'title' => 'Sự trỗi dậy từ tro tàn: Một kỷ nguyên mới bắt đầu',
                'severity' => 'critical',
                'payload' => [],
            ];
        }

        return $events;
    }

    private function translateLifecycle(LifecycleState $state): string
    {
        return match($state) {
            LifecycleState::Emerging => 'Khởi sinh',
            LifecycleState::Stable => 'Ổn định',
            LifecycleState::Dominant => 'Thống trị',
            LifecycleState::Ascended => 'Thăng hoa',
            LifecycleState::Transformed => 'Biến hình',
            LifecycleState::Dormant => 'Ngủ đông',
        };
    }
}
