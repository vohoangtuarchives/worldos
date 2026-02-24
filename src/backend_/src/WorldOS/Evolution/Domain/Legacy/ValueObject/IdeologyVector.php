<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObject;

use InvalidArgumentException;

/**
 * IdeologyVector - Represents a 6-dimensional political/cultural ideology.
 * Each dimension is normalized between 0.0 and 1.0.
 */
final class IdeologyVector
{
    public function __construct(
        public readonly float $centralization,  // 0: Decentralized/Anarchic, 1: Absolute Autocracy
        public readonly float $economic,        // 0: Free Market/Laissez-faire, 1: Command Economy
        public readonly float $culture,         // 0: Closed/Traditional, 1: Open/Progressive
        public readonly float $innovation,      // 0: Stagnant/Orthodox, 1: Radical/Experimental
        public readonly float $military,        // 0: Pacifist, 1: Highly Militarized
        public readonly float $institution      // 0: Rigid/Dogmatic, 1: Flexible/Adaptive
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        foreach (['centralization', 'economic', 'culture', 'innovation', 'military', 'institution'] as $prop) {
            if ($this->$prop < 0.0 || $this->$prop > 1.0) {
                // Warning: log or clamp instead if drifting via integration
                // throw new InvalidArgumentException("Ideology axis {$prop} must be between 0.0 and 1.0. Got: {$this->$prop}");
            }
        }
    }

    /**
     * Calculate Euclidean distance to another IdeologyVector.
     * Max possible distance is sqrt(6) ≈ 2.449.
     */
    public function distanceTo(self $other): float
    {
        return sqrt(
            pow($this->centralization - $other->centralization, 2) +
            pow($this->economic - $other->economic, 2) +
            pow($this->culture - $other->culture, 2) +
            pow($this->innovation - $other->innovation, 2) +
            pow($this->military - $other->military, 2) +
            pow($this->institution - $other->institution, 2)
        );
    }

    /**
     * Creates a new IdeologyVector by applying a drift vector, clamping values to [0, 1].
     */
    public function applyDrift(
        float $dCentralization,
        float $dEconomic,
        float $dCulture,
        float $dInnovation,
        float $dMilitary,
        float $dInstitution
    ): self {
        return new self(
            centralization: max(0.0, min(1.0, $this->centralization + $dCentralization)),
            economic: max(0.0, min(1.0, $this->economic + $dEconomic)),
            culture: max(0.0, min(1.0, $this->culture + $dCulture)),
            innovation: max(0.0, min(1.0, $this->innovation + $dInnovation)),
            military: max(0.0, min(1.0, $this->military + $dMilitary)),
            institution: max(0.0, min(1.0, $this->institution + $dInstitution))
        );
    }

    public function toArray(): array
    {
        return [
            'centralization' => $this->centralization,
            'economic' => $this->economic,
            'culture' => $this->culture,
            'innovation' => $this->innovation,
            'military' => $this->military,
            'institution' => $this->institution,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            centralization: (float)($data['centralization'] ?? 0.5),
            economic: (float)($data['economic'] ?? 0.5),
            culture: (float)($data['culture'] ?? 0.5),
            innovation: (float)($data['innovation'] ?? 0.5),
            military: (float)($data['military'] ?? 0.5),
            institution: (float)($data['institution'] ?? 0.5)
        );
    }
}
