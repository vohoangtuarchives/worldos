<?php

declare(strict_types=1);

namespace App\Domains\Material\ValueObjects;

use App\Domains\Material\ValueObjects\MaterialState;

final readonly class MaterialInstance
{
    public function __construct(
        public readonly string $id,
        public readonly string $materialId,
        public readonly string $worldId,
        public readonly float $strengthLevel,
        public readonly float $durability,
        public readonly float $purity,
        public readonly string $location,
        public readonly ?string $owner,
        public readonly \DateTime $createdAt,
        public readonly ?\DateTime $lastUsedAt,
        public readonly ?\DateTime $retiredAt,
        public readonly array $metadata,
        public readonly MaterialState $state = MaterialState::STABLE,
        public readonly float $instability = 0.0,
        public readonly float $corruption = 0.0,
        public readonly ?string $retirementReason = null,
    ) {
        $this->validateProperties();
    }

    private function validateProperties(): void
    {
        if ($this->strengthLevel < 0 || $this->strengthLevel > 10) {
            throw new \InvalidArgumentException('Strength level must be between 0 and 10');
        }

        if ($this->durability < 0 || $this->durability > 100) {
            throw new \InvalidArgumentException('Durability must be between 0 and 100');
        }

        if ($this->purity < 0 || $this->purity > 1) {
            throw new \InvalidArgumentException('Purity must be between 0 and 1');
        }

        if ($this->instability < 0 || $this->instability > 1) {
            throw new \InvalidArgumentException('Instability must be between 0 and 1');
        }

        if ($this->corruption < 0 || $this->corruption > 1) {
            throw new \InvalidArgumentException('Corruption must be between 0 and 1');
        }
    }

    public function isRetired(): bool
    {
        return $this->retiredAt !== null;
    }

    public function isActive(): bool
    {
        return !$this->isRetired() && $this->durability > 0;
    }

    public function isBroken(): bool
    {
        return $this->durability <= 0;
    }

    public function isDamaged(): bool
    {
        return $this->durability < 50 && $this->durability > 0;
    }

    public function isWorn(): bool
    {
        return $this->durability < 80 && $this->durability >= 50;
    }

    public function isOld(): bool
    {
        if ($this->createdAt === null) {
            return false;
        }

        $age = $this->createdAt->diff(now())->days;
        return $age > 30; // Older than 30 days
    }

    public function isUnstable(): bool
    {
        return $this->instability > 0.5;
    }

    public function isCorrupted(): bool
    {
        return $this->corruption > 0.3;
    }

    public function isMagical(): bool
    {
        return $this->metadata['magical'] ?? false;
    }

    public function isFragile(): bool
    {
        return $this->metadata['fragile'] ?? false;
    }

    public function isUnderutilized(): bool
    {
        if ($this->lastUsedAt === null) {
            return $this->createdAt->diff(now())->days > 7; // Never used for 7 days
        }

        return $this->lastUsedAt->diff(now())->days > 14; // Not used for 14 days
    }

    public function isRedundant(): bool
    {
        // Simplified - would check against other instances of same material
        return $this->metadata['redundant'] ?? false;
    }

    public function materialType(): string
    {
        return $this->metadata['type'] ?? 'unknown';
    }

    public function getValue(): float
    {
        $baseValue = $this->strengthLevel * 10;
        $durabilityMultiplier = $this->durability / 100.0;
        $purityMultiplier = $this->purity;
        $instabilityPenalty = 1 - ($this->instability * 0.5);
        $corruptionPenalty = 1 - ($this->corruption * 0.7);

        return $baseValue * $durabilityMultiplier * $purityMultiplier * $instabilityPenalty * $corruptionPenalty;
    }

    public function damage(float $amount): self
    {
        $newDurability = max(0, $this->durability - ($amount * 100));
        $newState = $this->calculateStateFromDurability($newDurability);

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $newDurability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            now(),
            $this->retiredAt,
            $this->metadata,
            $newState,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function degrade(float $amount): self
    {
        $newDurability = max(0, $this->durability - ($amount * 100));
        $newState = $this->calculateStateFromDurability($newDurability);

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $newDurability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            $newState,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function decay(float $amount): self
    {
        $newStrength = max(0, $this->strengthLevel - $amount);
        $newPurity = max(0, $this->purity - ($amount * 0.1));

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $newStrength,
            $this->durability,
            $newPurity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            $this->state,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function destroy(): self
    {
        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            0.0,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            MaterialState::BROKEN,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function retire(string $reason): self
    {
        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $this->durability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            now(),
            $this->metadata,
            MaterialState::RETIRED,
            $this->instability,
            $this->corruption,
            $reason,
        );
    }

    public function transferTo(string $newWorldId, ?string $newOwner = null): self
    {
        return new self(
            $this->id,
            $this->materialId,
            $newWorldId,
            $this->strengthLevel,
            $this->durability,
            $this->purity,
            $this->location,
            $newOwner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            $this->state,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function upgrade(array $upgrades): self
    {
        $newStrength = $this->strengthLevel;
        $newDurability = $this->durability;
        $newPurity = $this->purity;
        $newMetadata = $this->metadata;

        foreach ($upgrades as $upgrade) {
            match ($upgrade['type']) {
                'strength' => $newStrength = min(10, $newStrength + $upgrade['amount']),
                'durability' => $newDurability = min(100, $newDurability + ($upgrade['amount'] * 100)),
                'purity' => $newPurity = min(1.0, $newPurity + $upgrade['amount']),
                'enchantment' => $newMetadata['magical'] = true,
                'reinforcement' => $newMetadata['fragile'] = false,
                default => null
            };
        }

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $newStrength,
            $newDurability,
            $newPurity,
            $this->location,
            $this->owner,
            $this->createdAt,
            now(),
            $this->retiredAt,
            $newMetadata,
            $this->state,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function updateState(MaterialState $newState): self
    {
        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $this->durability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            $newState,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function addInstability(float $amount): self
    {
        $newInstability = min(1.0, $this->instability + $amount);
        $newState = $newInstability > 0.5 ? MaterialState::UNSTABLE : $this->state;

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $this->durability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            $newState,
            $newInstability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function corrupt(float $amount): self
    {
        $newCorruption = min(1.0, $this->corruption + $amount);
        $newState = $newCorruption > 0.3 ? MaterialState::CORRUPTED : $this->state;

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $this->durability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            $newState,
            $this->instability,
            $newCorruption,
            $this->retirementReason,
        );
    }

    public function contaminate(float $amount): self
    {
        $newPurity = max(0, $this->purity - $amount);

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $this->durability,
            $newPurity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            $this->state,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function deplete(float $amount): self
    {
        $newStrength = max(0, $this->strengthLevel - $amount);

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $newStrength,
            $this->durability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            $this->lastUsedAt,
            $this->retiredAt,
            $this->metadata,
            $this->state,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function steal(): self
    {
        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $this->durability,
            $this->purity,
            'stolen',
            null, // No owner when stolen
            $this->createdAt,
            now(),
            $this->retiredAt,
            array_merge($this->metadata, ['stolen' => true]),
            $this->state,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function enchant(float $amount): self
    {
        $newMetadata = array_merge($this->metadata, [
            'magical' => true,
            'enchantment_level' => ($this->metadata['enchantment_level'] ?? 0) + $amount
        ]);

        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $this->durability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            now(),
            $this->retiredAt,
            $newMetadata,
            $this->state,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function use(): self
    {
        return new self(
            $this->id,
            $this->materialId,
            $this->worldId,
            $this->strengthLevel,
            $this->durability,
            $this->purity,
            $this->location,
            $this->owner,
            $this->createdAt,
            now(),
            $this->retiredAt,
            $this->metadata,
            $this->state,
            $this->instability,
            $this->corruption,
            $this->retirementReason,
        );
    }

    public function differsFrom(self $other): bool
    {
        return $this->durability !== $other->durability ||
               $this->strengthLevel !== $other->strengthLevel ||
               $this->purity !== $other->purity ||
               $this->state !== $other->state ||
               $this->instability !== $other->instability ||
               $this->corruption !== $other->corruption ||
               $this->location !== $other->location ||
               $this->owner !== $other->owner;
    }

    private function calculateStateFromDurability(float $durability): MaterialState
    {
        return match (true) {
            $durability <= 0 => MaterialState::BROKEN,
            $durability < 20 => MaterialState::DAMAGED,
            $durability < 50 => MaterialState::WORN,
            $this->isUnstable() => MaterialState::UNSTABLE,
            $this->isCorrupted() => MaterialState::CORRUPTED,
            default => MaterialState::STABLE
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'material_id' => $this->materialId,
            'world_id' => $this->worldId,
            'strength_level' => $this->strengthLevel,
            'durability' => $this->durability,
            'purity' => $this->purity,
            'location' => $this->location,
            'owner' => $this->owner,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'last_used_at' => $this->lastUsedAt?->format('Y-m-d H:i:s'),
            'retired_at' => $this->retiredAt?->format('Y-m-d H:i:s'),
            'state' => $this->state->value,
            'instability' => $this->instability,
            'corruption' => $this->corruption,
            'retirement_reason' => $this->retirementReason,
            'metadata' => $this->metadata,
            'value' => $this->getValue(),
            'is_active' => $this->isActive(),
            'is_broken' => $this->isBroken(),
            'is_damaged' => $this->isDamaged(),
            'is_magical' => $this->isMagical(),
            'is_fragile' => $this->isFragile(),
            'is_underutilized' => $this->isUnderutilized(),
        ];
    }
}
