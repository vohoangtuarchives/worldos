<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Entity;

final class World
{
    private function __construct(
        private string $id,
        private string $name,
        private string $status = 'unknown',
        private string $healthStatus = 'unknown',
        private int $currentTick = 0,
        private string $originType = 'unknown',
        private string $preset = 'default',
        private array $config = [],
        private array $geneVector = [],
    ) {
    }

    public static function create(
        string $name,
        ?string $id = null,
        string $status = 'unknown',
        string $healthStatus = 'unknown',
        int $currentTick = 0,
        string $originType = 'unknown',
        string $preset = 'default',
        array $config = [],
        array $geneVector = [],
    ): self {
        return new self(
            $id ?? self::generateId(),
            $name,
            $status,
            $healthStatus,
            $currentTick,
            $originType,
            $preset,
            $config,
            $geneVector
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getHealthStatus(): string
    {
        return $this->healthStatus;
    }

    public function getCurrentTick(): int
    {
        return $this->currentTick;
    }

    public function getOriginType(): string
    {
        return $this->originType;
    }

    public function getPreset(): string
    {
        return $this->preset;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getGeneVector(): array
    {
        return $this->geneVector;
    }

    private static function generateId(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
