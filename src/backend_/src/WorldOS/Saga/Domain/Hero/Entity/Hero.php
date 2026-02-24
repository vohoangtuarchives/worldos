<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\Entity;

use WorldOS\Saga\Domain\Hero\ValueObject\HeroProfile;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroState;

/**
 * The V5 Hero Entity.
 * Integrates the legacy identification with the new dynamical system.
 */
final class Hero
{
    private function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $worldId,
        private readonly HeroProfile $profile,
        private HeroState $state
    ) {
    }

    public static function create(
        string $name,
        string $worldId,
        HeroProfile $profile,
        ?HeroState $state = null,
        ?string $id = null
    ): self {
        return new self(
            $id ?? self::generateId(),
            $name,
            $worldId,
            $profile,
            $state ?? HeroState::genesis($profile)
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

    public function getProfile(): HeroProfile
    {
        return $this->profile;
    }

    public function getState(): HeroState
    {
        return $this->state;
    }

    public function updateState(HeroState $newState): void
    {
        $this->state = $newState;
    }

    private static function generateId(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
