<?php

namespace App\Domains\Epistemology\ValueObjects;

use InvalidArgumentException;

class EpistemicIndex
{
    public function __construct(
        public readonly float $instability, // 0.0 to 1.0
        public readonly float $clarity // 0.0 to 1.0 (inverse of fog)
    ) {
        if ($instability < 0.0 || $instability > 1.0 || $clarity < 0.0 || $clarity > 1.0) {
            throw new InvalidArgumentException("Indices must be between 0.0 and 1.0");
        }
    }

    public function calculateDistortionProbability(): float
    {
        // High instability and low clarity vastly increases the chance of historical distortion
        return $this->instability * (1.0 - $this->clarity);
    }

    public function toArray(): array
    {
        return [
            'instability' => $this->instability,
            'clarity' => $this->clarity,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (float)($data['instability'] ?? 0.0),
            (float)($data['clarity'] ?? 1.0)
        );
    }
}
