<?php

declare(strict_types=1);

namespace App\WorldOS\Style\ValueObjects;

use InvalidArgumentException;

/**
 * Style Vector — 4D genre parameter space.
 *
 * From docs §2.3: Uses 4 continuous vectors instead of static labels.
 *   ontology     — what exists (magic vs tech vs hybrid)
 *   epistemic    — how knowledge works (mystical vs empirical)
 *   civilization — social structure (tribal vs imperial vs networked)
 *   energy       — power density (mạt pháp vs linh khí sung túc)
 *
 * All dimensions normalized [0,1]. Immutable Value Object.
 */
final readonly class StyleVector
{
    public function __construct(
        public float $ontology,      // 0=pure_tech, 1=pure_magic
        public float $epistemic,     // 0=empirical, 1=mystical
        public float $civilization,  // 0=tribal, 0.5=feudal, 1=networked
        public float $energy,        // 0=mạt_pháp, 1=linh_khí_dồi_dào
    ) {
        $this->validate();
    }

    /**
     * @param array<string, float> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ontology: (float) ($data['ontology'] ?? 0.5),
            epistemic: (float) ($data['epistemic'] ?? 0.5),
            civilization: (float) ($data['civilization'] ?? 0.5),
            energy: (float) ($data['energy'] ?? 0.5),
        );
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'ontology' => $this->ontology,
            'epistemic' => $this->epistemic,
            'civilization' => $this->civilization,
            'energy' => $this->energy,
        ];
    }

    /**
     * Euclidean distance to another StyleVector.
     */
    public function distanceTo(self $other): float
    {
        return sqrt(
            ($this->ontology - $other->ontology) ** 2
            + ($this->epistemic - $other->epistemic) ** 2
            + ($this->civilization - $other->civilization) ** 2
            + ($this->energy - $other->energy) ** 2
        );
    }

    /**
     * Interpolate between this and another vector (for style blending).
     */
    public function lerp(self $target, float $t): self
    {
        $t = max(0.0, min(1.0, $t));

        return new self(
            ontology: $this->ontology + ($target->ontology - $this->ontology) * $t,
            epistemic: $this->epistemic + ($target->epistemic - $this->epistemic) * $t,
            civilization: $this->civilization + ($target->civilization - $this->civilization) * $t,
            energy: $this->energy + ($target->energy - $this->energy) * $t,
        );
    }

    private function validate(): void
    {
        foreach ($this->toArray() as $name => $value) {
            if ($value < 0.0 || $value > 1.0) {
                throw new InvalidArgumentException(
                    "StyleVector dimension '{$name}' must be in [0.0, 1.0], got: {$value}"
                );
            }
        }
    }
}
