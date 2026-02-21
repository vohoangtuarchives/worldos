<?php

declare(strict_types=1);

namespace Tuzy\Domain\Epistemology\ValueObject;

use InvalidArgumentException;

/**
 * Epistemic state: instability and clarity (0.0–1.0).
 */
readonly class EpistemicIndex
{
    public function __construct(
        public float $instability = 0.0,
        public float $clarity = 1.0,
    ) {
        if ($instability < 0.0 || $instability > 1.0 || $clarity < 0.0 || $clarity > 1.0) {
            throw new InvalidArgumentException('Indices must be between 0.0 and 1.0');
        }
    }

    public function calculateDistortionProbability(): float
    {
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
            (float) ($data['instability'] ?? 0.0),
            (float) ($data['clarity'] ?? 1.0),
        );
    }
}
