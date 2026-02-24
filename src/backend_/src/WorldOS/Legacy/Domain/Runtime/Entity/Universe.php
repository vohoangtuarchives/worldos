<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Runtime\Entity;

final class Universe
{
    private function __construct(
        private string $id,
        private string $name,
        private string $worldId,
        private string $sagaId,
        private int $age = 0,
        private string $status = 'unknown',
        private array $stateVector = [],
        private float $entropy = 0.0,
        private float $stabilityIndex = 1.0,
    ) {
    }

    public static function create(
        string $name,
        string $worldId,
        string $sagaId,
        ?string $id = null,
        int $age = 0,
        string $status = 'unknown',
        array $stateVector = [],
        float $entropy = 0.0,
        float $stabilityIndex = 1.0
    ): self {
        return new self(
            $id ?? self::generateId(),
            $name,
            $worldId,
            $sagaId,
            $age,
            $status,
            $stateVector,
            $entropy,
            $stabilityIndex
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWorldId(): string
    {
        return $this->worldId;
    }

    public function getSagaId(): string
    {
        return $this->sagaId;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStateVector(): array
    {
        return $this->stateVector;
    }

    public function getEntropy(): float
    {
        return $this->entropy;
    }

    public function getStabilityIndex(): float
    {
        return $this->stabilityIndex;
    }

    private static function generateId(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
