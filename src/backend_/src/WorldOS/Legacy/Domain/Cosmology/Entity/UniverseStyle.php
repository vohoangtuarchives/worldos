<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\Entity;

final class UniverseStyle
{
    private function __construct(
        private string $id,
        private string $name,
        private string $worldId,
    ) {
    }

    public static function create(string $name, string $worldId, ?string $id = null): self
    {
        return new self(
            $id ?? self::generateId(),
            $name,
            $worldId,
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

    private static function generateId(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
