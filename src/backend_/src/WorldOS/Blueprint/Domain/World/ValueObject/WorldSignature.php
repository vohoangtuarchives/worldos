<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\ValueObject;

/**
 * WorldSignature represents the deterministic hash of the PhysicsCore and NarrativeTopology.
 * It ensures immutability and tracks exact law lineages.
 */
final class WorldSignature
{
    private function __construct(
        private readonly string $hash,
        private readonly string $physicsHash,
        private readonly string $narrativeHash
    ) {
    }

    public static function generate(PhysicsCore $physicsCore, NarrativeTopology $narrativeTopology): self
    {
        // Sort arrays to guarantee deterministic hashing of components
        $physicsData = $physicsCore->toArray();
        array_multisort($physicsData);
        $physicsHash = hash('sha256', json_encode($physicsData, JSON_THROW_ON_ERROR));

        $narrativeData = $narrativeTopology->toArray();
        array_multisort($narrativeData);
        $narrativeHash = hash('sha256', json_encode($narrativeData, JSON_THROW_ON_ERROR));
        
        $combinedHash = hash('sha256', $physicsHash . $narrativeHash);

        return new self($combinedHash, $physicsHash, $narrativeHash);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getPhysicsHash(): string
    {
        return $this->physicsHash;
    }

    public function getNarrativeHash(): string
    {
        return $this->narrativeHash;
    }

    public function equals(WorldSignature $other): bool
    {
        return $this->hash === $other->getHash();
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
