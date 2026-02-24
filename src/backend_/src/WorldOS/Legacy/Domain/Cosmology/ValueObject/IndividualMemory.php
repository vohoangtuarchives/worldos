<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

/**
 * IndividualMemory — Historical Inertia Vector for a single attractor.
 * Tracks cumulative rebirth gain, instability, morph history. Produces MemoryBias vector.
 */
final class IndividualMemory
{
    private const LAMBDA = 0.985;
    private const GAMMA = 0.15;
    private const MAX_BIAS_MAGNITUDE = 0.25;

    public function __construct(
        private readonly string $attractorCode,
        private readonly float $cumulativeRebirthGain,
        private readonly float $cumulativeInstability,
        private readonly float $morphIntensityAccumulator,
        /** @var array{entropy: float, energy: float, causality: float, strain: float, stability: float} */
        private readonly array $inertiaVector,
        private readonly int $eventCount,
    ) {}

    public static function initial(string $attractorCode): self
    {
        return new self(
            attractorCode: $attractorCode,
            cumulativeRebirthGain: 0.0,
            cumulativeInstability: 0.0,
            morphIntensityAccumulator: 0.0,
            inertiaVector: ['entropy' => 0.0, 'energy' => 0.0, 'causality' => 0.0, 'strain' => 0.0, 'stability' => 0.0],
            eventCount: 0,
        );
    }

    public function recordEvent(array $eventVector): self
    {
        $newInertia = [];
        foreach ($this->inertiaVector as $dim => $value) {
            $decayed = self::LAMBDA * $value;
            $newInertia[$dim] = $decayed + ($eventVector[$dim] ?? 0.0);
        }
        return new self(
            attractorCode: $this->attractorCode,
            cumulativeRebirthGain: $this->cumulativeRebirthGain,
            cumulativeInstability: $this->cumulativeInstability,
            morphIntensityAccumulator: $this->morphIntensityAccumulator,
            inertiaVector: $newInertia,
            eventCount: $this->eventCount + 1,
        );
    }

    public function recordRebirth(float $rebirthGain, float $morphIntensity): self
    {
        return new self(
            attractorCode: $this->attractorCode,
            cumulativeRebirthGain: $this->cumulativeRebirthGain + $rebirthGain,
            cumulativeInstability: $this->cumulativeInstability,
            morphIntensityAccumulator: $this->morphIntensityAccumulator + $morphIntensity,
            inertiaVector: $this->inertiaVector,
            eventCount: $this->eventCount + 1,
        );
    }

    public function recordInstability(float $intensity): self
    {
        return new self(
            attractorCode: $this->attractorCode,
            cumulativeRebirthGain: $this->cumulativeRebirthGain,
            cumulativeInstability: $this->cumulativeInstability + $intensity,
            morphIntensityAccumulator: $this->morphIntensityAccumulator,
            inertiaVector: $this->inertiaVector,
            eventCount: $this->eventCount + 1,
        );
    }

    public function decay(): self
    {
        $decayed = [];
        foreach ($this->inertiaVector as $dim => $value) {
            $decayed[$dim] = self::LAMBDA * $value;
        }
        return new self(
            attractorCode: $this->attractorCode,
            cumulativeRebirthGain: $this->cumulativeRebirthGain,
            cumulativeInstability: $this->cumulativeInstability,
            morphIntensityAccumulator: $this->morphIntensityAccumulator,
            inertiaVector: $decayed,
            eventCount: $this->eventCount,
        );
    }

    public function memoryBias(): array
    {
        $bias = [];
        $magnitudeSq = 0.0;
        foreach ($this->inertiaVector as $dim => $value) {
            $b = self::GAMMA * $value;
            $bias[$dim] = $b;
            $magnitudeSq += $b * $b;
        }
        $magnitude = sqrt($magnitudeSq);
        if ($magnitude > self::MAX_BIAS_MAGNITUDE && $magnitude > 0.0001) {
            $scale = self::MAX_BIAS_MAGNITUDE / $magnitude;
            foreach ($bias as $dim => $value) {
                $bias[$dim] = $value * $scale;
            }
        }
        return $bias;
    }

    public function identityKarmaIndex(): float
    {
        if ($this->eventCount === 0) return 0.0;
        return round(0.6 * $this->morphIntensityAccumulator + 0.4 * $this->cumulativeRebirthGain, 4);
    }

    public function getAttractorCode(): string { return $this->attractorCode; }
    public function getCumulativeRebirthGain(): float { return $this->cumulativeRebirthGain; }
    public function getCumulativeInstability(): float { return $this->cumulativeInstability; }
    public function getMorphIntensityAccumulator(): float { return $this->morphIntensityAccumulator; }
    public function getInertiaVector(): array { return $this->inertiaVector; }
    public function getEventCount(): int { return $this->eventCount; }

    public function toArray(): array
    {
        return [
            'attractor_code' => $this->attractorCode,
            'cumulative_rebirth_gain' => $this->cumulativeRebirthGain,
            'cumulative_instability' => $this->cumulativeInstability,
            'morph_intensity_accumulator' => $this->morphIntensityAccumulator,
            'inertia_vector' => $this->inertiaVector,
            'event_count' => $this->eventCount,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            attractorCode: $data['attractor_code'],
            cumulativeRebirthGain: $data['cumulative_rebirth_gain'] ?? 0.0,
            cumulativeInstability: $data['cumulative_instability'] ?? 0.0,
            morphIntensityAccumulator: $data['morph_intensity_accumulator'] ?? 0.0,
            inertiaVector: $data['inertia_vector'] ?? ['entropy' => 0, 'energy' => 0, 'causality' => 0, 'strain' => 0, 'stability' => 0],
            eventCount: $data['event_count'] ?? 0,
        );
    }
}
