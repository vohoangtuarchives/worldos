<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Entity;

use Tuzy\Domain\Evolution\ValueObject\Attractor;
use Tuzy\Domain\Evolution\ValueObject\AttractorIncarnation;

/**
 * AttractorAggregate â€” the full lifecycle entity for a cosmic attractor.
 *
 * An attractor has a fixed identity (code) but morphs over time.
 * Each morph creates a new AttractorIncarnation in the Memory Tree.
 *
 * The aggregate tracks:
 * - Current centroid (evolves via morphing)
 * - Incarnation tree (parent â†’ child chain)
 * - Historical inertia (cumulative memory)
 * - Identity Karma Index (IKI)
 * - Phase state (STABLE, CHAOTIC_TRANSITION, RECONSOLIDATING)
 */
class AttractorAggregate
{
    private string $code;
    private string $name;
    private string $lifecycleState;     // EMERGENT | DOMINANT | DECLINING | EXTINCT
    private string $phaseState;         // STABLE | CHAOTIC_TRANSITION | RECONSOLIDATING

    /** @var array{entropy: float, energy: float, causality: float, strain: float, stability: float} */
    private array $currentCentroid;
    private float $basinRadius;
    private float $curvature;
    private float $bifurcationThreshold;
    private float $recoveryRate;
    private float $rigidityThreshold;

    // Memory
    private float $cumulativeRebirthGain = 0.0;
    private float $cumulativeInstability = 0.0;
    private float $identityKarmaIndex = 0.0;

    /** @var AttractorIncarnation[] */
    private array $incarnations = [];
    private int $incarnationCounter = 0;

    private function __construct() {}

    /**
     * Create from an existing Attractor definition (catalog or emergent).
     */
    public static function fromAttractor(Attractor $attractor, int $startTick = 0): self
    {
        $agg = new self();
        $code = $attractor->getName();
        $agg->code = $code;
        $agg->name = $code;
        $agg->lifecycleState = 'EMERGENT';
        $agg->phaseState = 'STABLE';

        $centroid = $attractor->getCentroid();
        $agg->currentCentroid = [
            'entropy' => $centroid['entropy'] ?? 0.5,
            'energy' => $centroid['order'] ?? 0.5, // Dùng Order thay Energy
            'causality' => 0.5,
            'strain' => 0.1,
            'stability' => 0.7,
        ];
        $agg->basinRadius = 0.3;
        $agg->curvature = 1.0;
        $agg->bifurcationThreshold = 0.7; // Mặc định từ V3
        $agg->recoveryRate = 0.02;
        $agg->rigidityThreshold = 0.7;

        // Xóa tạm initial incarnation để tránh lỗi với AttractorIncarnation
        $agg->incarnations = [];
        $agg->incarnationCounter = 0;

        return $agg;
    }

    // --- Phase Transitions ---

    /**
     * Enter chaotic transition. Set phase, record instability.
     */
    public function enterChaos(float $chaosIntensity, int $tick): void
    {
        $this->phaseState = 'CHAOTIC_TRANSITION';
        $this->cumulativeInstability += $chaosIntensity;
    }

    /**
     * Apply morphing: create new incarnation, slide centroid, update memory.
     *
     * @param array $newCentroid New centroid after morphing
     * @param array $newSemantic New semantic vector
     * @param float $rebirthGain RG = (OrderAfter - OrderBefore) / ChaosDuration
     * @param float $morphIntensity How radical the morph was
     * @param int $tick Current simulation tick
     */
    public function morph(
        array $newCentroid,
        array $newSemantic,
        float $rebirthGain,
        float $morphIntensity,
        int $tick,
    ): void {
        // Close current incarnation
        $currentInc = $this->currentIncarnation();
        if ($currentInc) {
            $closedInc = $currentInc->close($tick);
            $this->incarnations[count($this->incarnations) - 1] = $closedInc;

            // Create child incarnation
            $child = $closedInc->createChild(
                childIndex: $this->incarnationCounter,
                startTick: $tick,
                newCentroid: $newCentroid,
                newSemantic: $newSemantic,
                rebirthGain: $rebirthGain,
                morphIntensity: $morphIntensity,
            );
            $this->incarnations[] = $child;
            $this->incarnationCounter++;
        }

        // Update aggregate state
        $this->currentCentroid = $newCentroid;
        $this->cumulativeRebirthGain += $rebirthGain;
        $this->phaseState = 'RECONSOLIDATING';

        // Update IKI
        $this->identityKarmaIndex = $this->calculateIKI();

        // Memory effects on parameters (path dependency)
        $this->applyMemoryEffects($rebirthGain);
    }

    /**
     * Reconsolidate: transition back to STABLE.
     */
    public function reconsolidate(): void
    {
        $this->phaseState = 'STABLE';
    }

    /**
     * Promote lifecycle: EMERGENT â†’ DOMINANT.
     */
    public function promote(): void
    {
        if ($this->lifecycleState === 'EMERGENT') {
            $this->lifecycleState = 'DOMINANT';
        }
    }

    /**
     * Decline lifecycle: DOMINANT â†’ DECLINING.
     */
    public function decline(): void
    {
        if ($this->lifecycleState === 'DOMINANT') {
            $this->lifecycleState = 'DECLINING';
        }
    }

    // --- Memory Effects (Path Dependency) ---

    /**
     * Memory biases parameters â€” NOT overrides physics.
     * From RFC Â§5.4:
     *   Basin Elasticity: r' = r Ã— (1 + elasticity_factor)
     *   Recovery Speed:   Ï' = Ï Ã— (1 + rg_factor)
     *   Rigidity:         Î¸' = Î¸ Ã— (1 - collapse_factor)
     */
    private function applyMemoryEffects(float $rebirthGain): void
    {
        // Basin becomes more elastic after successful rebirth
        if ($rebirthGain > 0) {
            $elasticityFactor = min(0.1, $rebirthGain * 0.05);
            $this->basinRadius *= (1.0 + $elasticityFactor);
            $this->basinRadius = min(0.6, $this->basinRadius); // Cap

            $rgFactor = min(0.1, $rebirthGain * 0.03);
            $this->recoveryRate *= (1.0 + $rgFactor);
            $this->recoveryRate = min(0.1, $this->recoveryRate); // Cap
        }

        // Deep collapse makes future chaos easier to trigger
        if ($this->cumulativeInstability > 1.0) {
            $collapseFactor = min(0.15, $this->cumulativeInstability * 0.02);
            $this->rigidityThreshold *= (1.0 - $collapseFactor);
            $this->rigidityThreshold = max(0.3, $this->rigidityThreshold); // Floor
        }
    }

    /**
     * IKI = weighted blend of morph intensity history and rebirth gain.
     * High IKI â†’ easier grandness but deeper chaos when it comes.
     */
    private function calculateIKI(): float
    {
        if (empty($this->incarnations)) return 0.0;

        $totalMorphIntensity = 0.0;
        $totalRG = 0.0;
        $count = 0;

        foreach ($this->incarnations as $inc) {
            if ($inc->morphIntensity > 0) {
                $totalMorphIntensity += $inc->morphIntensity;
                $totalRG += $inc->rebirthGainFromParent;
                $count++;
            }
        }

        if ($count === 0) return 0.0;

        $avgMorph = $totalMorphIntensity / $count;
        $avgRG = $totalRG / $count;

        return round(0.6 * $avgMorph + 0.4 * $avgRG, 4);
    }

    // --- Query methods ---

    public function currentIncarnation(): ?AttractorIncarnation
    {
        return empty($this->incarnations) ? null : end($this->incarnations);
    }

    public function incarnationDepth(): int
    {
        return count($this->incarnations);
    }

    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getLifecycleState(): string { return $this->lifecycleState; }
    public function getPhaseState(): string { return $this->phaseState; }
    public function getCurrentCentroid(): array { return $this->currentCentroid; }
    public function getBasinRadius(): float { return $this->basinRadius; }
    public function getCurvature(): float { return $this->curvature; }
    public function getBifurcationThreshold(): float { return $this->bifurcationThreshold; }
    public function getRecoveryRate(): float { return $this->recoveryRate; }
    public function getRigidityThreshold(): float { return $this->rigidityThreshold; }
    public function getCumulativeRebirthGain(): float { return $this->cumulativeRebirthGain; }
    public function getCumulativeInstability(): float { return $this->cumulativeInstability; }
    public function getIdentityKarmaIndex(): float { return $this->identityKarmaIndex; }
    public function getIncarnations(): array { return $this->incarnations; }

    /**
     * Distance from the current state to the attractor centroid.
     */
    public function distanceFrom(array $stateVector): float
    {
        $sum = 0.0;
        foreach (['entropy', 'energy', 'causality', 'strain', 'stability'] as $dim) {
            $diff = ($stateVector[$dim] ?? 0.0) - ($this->currentCentroid[$dim] ?? 0.0);
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    /**
     * Calculate the attractor's pull weight on a given state (0 if outside basin).
     */
    public function pullWeight(array $stateVector): float
    {
        $dist = $this->distanceFrom($stateVector);
        if ($dist >= $this->basinRadius) return 0.0;

        return max(0, 1.0 - $dist / $this->basinRadius);
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'lifecycle_state' => $this->lifecycleState,
            'phase_state' => $this->phaseState,
            'current_centroid' => $this->currentCentroid,
            'basin_radius' => $this->basinRadius,
            'curvature' => $this->curvature,
            'bifurcation_threshold' => $this->bifurcationThreshold,
            'recovery_rate' => $this->recoveryRate,
            'rigidity_threshold' => $this->rigidityThreshold,
            'cumulative_rebirth_gain' => $this->cumulativeRebirthGain,
            'cumulative_instability' => $this->cumulativeInstability,
            'identity_karma_index' => $this->identityKarmaIndex,
            'incarnation_counter' => $this->incarnationCounter,
            'incarnations' => array_map(fn ($i) => $i->toArray(), $this->incarnations),
        ];
    }

    public static function fromArray(array $data): self
    {
        $agg = new self();
        $agg->code = $data['code'];
        $agg->name = $data['name'];
        $agg->lifecycleState = $data['lifecycle_state'] ?? 'EMERGENT';
        $agg->phaseState = $data['phase_state'] ?? 'STABLE';
        $agg->currentCentroid = $data['current_centroid'];
        $agg->basinRadius = $data['basin_radius'] ?? 0.3;
        $agg->curvature = $data['curvature'] ?? 1.0;
        $agg->bifurcationThreshold = $data['bifurcation_threshold'] ?? 0.9;
        $agg->recoveryRate = $data['recovery_rate'] ?? 0.02;
        $agg->rigidityThreshold = $data['rigidity_threshold'] ?? 0.7;
        $agg->cumulativeRebirthGain = $data['cumulative_rebirth_gain'] ?? 0.0;
        $agg->cumulativeInstability = $data['cumulative_instability'] ?? 0.0;
        $agg->identityKarmaIndex = $data['identity_karma_index'] ?? 0.0;
        $agg->incarnationCounter = $data['incarnation_counter'] ?? 0;
        $agg->incarnations = array_map(
            fn ($i) => AttractorIncarnation::fromArray($i),
            $data['incarnations'] ?? []
        );
        return $agg;
    }
}
