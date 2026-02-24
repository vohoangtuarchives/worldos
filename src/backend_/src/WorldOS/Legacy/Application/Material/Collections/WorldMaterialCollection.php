<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Material\Collections;

use WorldOS\Legacy\Domain\Material\ValueObjects\MaterialInstance;
use WorldOS\Legacy\Domain\Material\ValueObject\MaterialState;
use Illuminate\Support\Collection;

final class WorldMaterialCollection
{
    /** @var MaterialInstance[] */
    private array $instances = [];
    private readonly string $worldId;
    private array $changes = [];

    public function __construct(string $worldId)
    {
        $this->worldId = $worldId;
    }

    public function add(MaterialInstance $instance): void
    {
        $this->instances[$instance->id] = $instance;
        $this->recordChange('add', $instance->id);
    }

    public function update(MaterialInstance $instance): void
    {
        if (isset($this->instances[$instance->id])) {
            $oldInstance = $this->instances[$instance->id];
            if ($oldInstance->differsFrom($instance)) {
                $this->instances[$instance->id] = $instance;
                $this->recordChange('update', $instance->id, $oldInstance, $instance);
            }
        }
    }

    public function remove(string $instanceId): void
    {
        if (isset($this->instances[$instanceId])) {
            unset($this->instances[$instanceId]);
            $this->recordChange('remove', $instanceId);
        }
    }

    public function count(): int
    {
        return count($this->instances);
    }

    public function isEmpty(): bool
    {
        return empty($this->instances);
    }

    public function worldId(): string
    {
        return $this->worldId;
    }

    public function all(): array
    {
        return array_values($this->instances);
    }

    public function getById(string $instanceId): ?MaterialInstance
    {
        return $this->instances[$instanceId] ?? null;
    }

    public function getByMaterialId(string $materialId): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->materialId === $materialId) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getByLocation(string $location): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->location === $location) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getByOwner(string $owner): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->owner === $owner) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getByState(MaterialState $state): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->state === $state) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getActive(): self
    {
        return $this->getByState(MaterialState::STABLE)
            ->merge($this->getByState(MaterialState::WORN))
            ->merge($this->getByState(MaterialState::UNSTABLE));
    }

    public function getRetired(): self
    {
        return $this->getByState(MaterialState::RETIRED);
    }

    public function getBroken(): self
    {
        return $this->getByState(MaterialState::BROKEN)
            ->merge($this->getByState(MaterialState::DAMAGED));
    }

    public function getDamaged(): self
    {
        return $this->getByState(MaterialState::DAMAGED);
    }

    public function getWorn(): self
    {
        return $this->getByState(MaterialState::WORN);
    }

    public function getUnstable(): self
    {
        return $this->getByState(MaterialState::UNSTABLE);
    }

    public function getCorrupted(): self
    {
        return $this->getByState(MaterialState::CORRUPTED);
    }

    public function getByType(string $materialType): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->materialType === $materialType) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getMagical(): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->isMagical()) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getFragile(): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->isFragile()) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getUnderutilized(): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->isUnderutilized()) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getRedundant(): self
    {
        $filtered = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            if ($instance->isRedundant()) {
                $filtered->add($instance);
            }
        }

        return $filtered;
    }

    public function getTypeBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->instances as $instance) {
            $type = $instance->materialType;
            $breakdown[$type] = ($breakdown[$type] ?? 0) + 1;
        }

        return $breakdown;
    }

    public function getLocationBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->instances as $instance) {
            $location = $instance->location;
            $breakdown[$location] = ($breakdown[$location] ?? 0) + 1;
        }

        return $breakdown;
    }

    public function getOwnerBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->instances as $instance) {
            $owner = $instance->owner ?? 'unowned';
            $breakdown[$owner] = ($breakdown[$owner] ?? 0) + 1;
        }

        return $breakdown;
    }

    public function getStateBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->instances as $instance) {
            $state = $instance->state->value;
            $breakdown[$state] = ($breakdown[$state] ?? 0) + 1;
        }

        return $breakdown;
    }

    public function getAverageDurability(): float
    {
        if (empty($this->instances)) {
            return 0.0;
        }

        $totalDurability = array_sum(array_map(fn($i) => $i->durability, $this->instances));
        
        return $totalDurability / count($this->instances);
    }

    public function getAverageStrength(): float
    {
        if (empty($this->instances)) {
            return 0.0;
        }

        $totalStrength = array_sum(array_map(fn($i) => $i->strengthLevel, $this->instances));
        
        return $totalStrength / count($this->instances);
    }

    public function getTotalValue(): float
    {
        return array_sum(array_map(fn($i) => $i->getValue(), $this->instances));
    }

    public function sortByDurability(): self
    {
        $sorted = new self($this->worldId);
        
        $sortedInstances = $this->instances;
        usort($sortedInstances, fn($a, $b) => $b->durability <=> $a->durability);
        
        foreach ($sortedInstances as $instance) {
            $sorted->add($instance);
        }

        return $sorted;
    }

    public function sortByStrength(): self
    {
        $sorted = new self($this->worldId);
        
        $sortedInstances = $this->instances;
        usort($sortedInstances, fn($a, $b) => $b->strengthLevel <=> $a->strengthLevel);
        
        foreach ($sortedInstances as $instance) {
            $sorted->add($instance);
        }

        return $sorted;
    }

    public function sortByValue(): self
    {
        $sorted = new self($this->worldId);
        
        $sortedInstances = $this->instances;
        usort($sortedInstances, fn($a, $b) => $b->getValue() <=> $a->getValue());
        
        foreach ($sortedInstances as $instance) {
            $sorted->add($instance);
        }

        return $sorted;
    }

    public function limit(int $count): self
    {
        $limited = new self($this->worldId);
        
        foreach (array_slice($this->instances, 0, $count) as $instance) {
            $limited->add($instance);
        }

        return $limited;
    }

    public function merge(self $other): self
    {
        $merged = new self($this->worldId);
        
        foreach ($this->instances as $instance) {
            $merged->add($instance);
        }
        
        foreach ($other->instances as $instance) {
            $merged->add($instance);
        }

        return $merged;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function clearChanges(): void
    {
        $this->changes = [];
    }

    private function recordChange(string $type, string $instanceId, ?MaterialInstance $old = null, ?MaterialInstance $new = null): void
    {
        $this->changes[] = [
            'type' => $type,
            'instance_id' => $instanceId,
            'timestamp' => now(),
            'old_state' => $old?->toArray(),
            'new_state' => $new?->toArray(),
        ];
    }

    public function getSummary(): array
    {
        return [
            'world_id' => $this->worldId,
            'total_instances' => $this->count(),
            'active_instances' => $this->getActive()->count(),
            'retired_instances' => $this->getRetired()->count(),
            'broken_instances' => $this->getBroken()->count(),
            'damaged_instances' => $this->getDamaged()->count(),
            'magical_instances' => $this->getMagical()->count(),
            'fragile_instances' => $this->getFragile()->count(),
            'average_durability' => $this->getAverageDurability(),
            'average_strength' => $this->getAverageStrength(),
            'total_value' => $this->getTotalValue(),
            'type_breakdown' => $this->getTypeBreakdown(),
            'location_breakdown' => $this->getLocationBreakdown(),
            'owner_breakdown' => $this->getOwnerBreakdown(),
            'state_breakdown' => $this->getStateBreakdown(),
        ];
    }

    public function toArray(): array
    {
        return array_map(fn($instance) => $instance->toArray(), $this->instances);
    }
}
