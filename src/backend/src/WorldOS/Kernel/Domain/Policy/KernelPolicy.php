<?php

declare(strict_types=1);

namespace WorldOS\Kernel\Domain\Policy;

use InvalidArgumentException;

/**
 * KernelPolicy represents the raw JSON/array Declarative DSL of the kernel.
 * It strictly holds configuration boundaries and mathematical rules before compilation.
 */
final class KernelPolicy
{
    private function __construct(
        private readonly string $version,
        private readonly array $stabilityBounds,
        private readonly array $evolutionRules,
        private readonly array $forkMechanics,
        private readonly array $weightFormulas
    ) {
    }

    public static function fromArray(array $config): self
    {
        self::validateStructure($config);

        return new self(
            $config['version'],
            $config['stability'] ?? [],
            $config['evolution'] ?? [],
            $config['fork'] ?? [],
            $config['weight'] ?? []
        );
    }

    private static function validateStructure(array $config): void
    {
        if (empty($config['version'])) {
            throw new InvalidArgumentException("KernelPolicy must have a 'version'.");
        }
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getStabilityBounds(): array
    {
        return $this->stabilityBounds;
    }

    public function getEvolutionRules(): array
    {
        return $this->evolutionRules;
    }

    public function getForkMechanics(): array
    {
        return $this->forkMechanics;
    }

    public function getWeightFormulas(): array
    {
        return $this->weightFormulas;
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'stability' => $this->stabilityBounds,
            'evolution' => $this->evolutionRules,
            'fork' => $this->forkMechanics,
            'weight' => $this->weightFormulas,
        ];
    }
}
