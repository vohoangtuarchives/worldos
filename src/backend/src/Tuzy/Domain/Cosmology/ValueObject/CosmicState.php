<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmology\ValueObject;

use InvalidArgumentException;

/**
 * CosmicState - Immutable Value Object representing the cosmic field at a moment in time.
 *
 * This is the "state vector" of the universe.
 * All transitions are deterministic: S(t+1) = F(S(t), energy(t)).
 */
final class CosmicState
{
    public function __construct(
        public readonly float $entropy,
        public readonly float $energy,
        public readonly float $causality,
        public readonly float $strain,
        public readonly float $stability,
        public readonly string $currentAttractor,
        public readonly int $year,
        public readonly ?string $currentIncarnationId = null,
        public readonly ?array $morphTargetCentroid = null,
        public readonly ?int $morphStartTick = null,
        public readonly float $morphIntensity = 1.0
    ) {
        $this->validate();
    }

    public static function defaultObservation(int $year = 0): self
    {
        return new self(
            entropy: 0.20,
            energy: 0.60,
            causality: 0.30,
            strain: 0.05,
            stability: 0.80,
            currentAttractor: 'EQUILIBRIUM',
            year: $year,
        );
    }

    public function evolve(float $externalEnergy, float $civilizationResonance = 0.0, int $deltaYears = 100): self
    {
        $ent = $this->entropy;
        $en = $externalEnergy;
        $cau = $this->causality;
        $str = $this->strain;
        $stab = $this->stability;

        $alpha = 0.0004;
        $beta = 0.0003;
        $strainFeedbackFactor = 0.002;

        for ($i = 0; $i < $deltaYears; $i++) {
            $entChange = ($alpha * $ent * (1.0 - $ent)) - ($beta * $en);
            $ent = max(0.0, min(1.0, $ent + $entChange));

            $cauChange = ($ent * 0.0003) + ($en * 0.0002) - 0.0001;
            $cau = max(0.0, min(2.0, $cau + $cauChange));

            $stab = 1.0 - $ent;

            $strainRunaway = $strainFeedbackFactor * $ent * $str;
            $strainResonance = $civilizationResonance * 0.0005;
            $strainRecovery = $stab * 0.0002;
            $strChange = $strainRunaway + $strainResonance - $strainRecovery;
            if ($str < 0.01 && $ent > 0.5) {
                $strChange += 0.0001;
            }
            $str = max(0.0, min(2.0, $str + $strChange));
        }

        $newAttractor = $this->currentAttractor;

        return new self(
            entropy: round($ent, 6),
            energy: round($en, 6),
            causality: round($cau, 6),
            strain: round($str, 6),
            stability: round($stab, 6),
            currentAttractor: $newAttractor,
            year: $this->year + $deltaYears,
        );
    }

    public function isCritical(float $resilience): bool
    {
        return ($this->strain > 0.9 && $resilience < 0.2);
    }

    public function cosmicTension(): float
    {
        return ($this->entropy * 0.3 + $this->causality * 0.3 + $this->strain * 0.4);
    }

    public function toArray(): array
    {
        return [
            'entropy' => $this->entropy,
            'energy' => $this->energy,
            'causality' => $this->causality,
            'strain' => $this->strain,
            'stability' => $this->stability,
            'current_attractor' => $this->currentAttractor,
            'year' => $this->year,
            'current_incarnation_id' => $this->currentIncarnationId,
            'morph_target_centroid' => $this->morphTargetCentroid,
            'morph_start_tick' => $this->morphStartTick,
            'morph_intensity' => $this->morphIntensity,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            entropy: (float) ($data['entropy'] ?? 0.2),
            energy: (float) ($data['energy'] ?? 0.6),
            causality: (float) ($data['causality'] ?? 0.3),
            strain: (float) ($data['strain'] ?? 0.05),
            stability: (float) ($data['stability'] ?? 0.8),
            currentAttractor: (string) ($data['current_attractor'] ?? 'EQUILIBRIUM'),
            year: (int) ($data['year'] ?? 0),
            currentIncarnationId: $data['current_incarnation_id'] ?? null,
            morphTargetCentroid: $data['morph_target_centroid'] ?? null,
            morphStartTick: isset($data['morph_start_tick']) ? (int) $data['morph_start_tick'] : null,
            morphIntensity: (float) ($data['morph_intensity'] ?? 1.0)
        );
    }

    private function validate(): void
    {
        if ($this->entropy < 0.0 || $this->entropy > 1.0) {
            throw new InvalidArgumentException("Entropy must be between 0.0 and 1.0, got {$this->entropy}");
        }
        if ($this->energy < 0.0 || $this->energy > 1.0) {
            throw new InvalidArgumentException("Energy must be between 0.0 and 1.0, got {$this->energy}");
        }
    }
}
