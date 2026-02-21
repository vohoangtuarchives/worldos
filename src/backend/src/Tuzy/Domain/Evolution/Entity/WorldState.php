<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Entity;

use Tuzy\Domain\Shared\Entity\AggregateRoot;
use Tuzy\Domain\Evolution\Event\EntropyCriticalReached;
use Tuzy\Domain\Evolution\ValueObject\CoreTruth;
use Tuzy\Domain\Evolution\ValueObject\CosmicState;
use Tuzy\Domain\Evolution\ValueObject\EnvironmentState;
use Tuzy\Domain\Evolution\Enum\WorldPhase;
use Tuzy\Domain\Evolution\ValueObject\LifeState;
use Tuzy\Domain\Evolution\Constant\PresetDescriptor;
use Tuzy\Domain\Evolution\ValueObject\WorldSnapshot;
use Tuzy\Domain\Evolution\Entity\Tension;

class WorldState extends AggregateRoot
{
    private WorldPhase $worldPhase;
    private LifeState $lifeState;

    public function __construct(
        string $id, 
        ?CosmicState $cosmicState = null,
        ?EnvironmentState $envState = null,
        int $year = 0,
        ?CoreTruth $coreTruth = null,
        ?WorldPhase $worldPhase = null,
        ?LifeState $lifeState = null
    ) {
        parent::__construct($id);
        $this->cosmicState = $cosmicState ?? CosmicState::defaultObservation($year);
        $this->envState = $envState ?? EnvironmentState::defaultObservation($year);
        $this->year = $year;
        $this->coreTruth = $coreTruth ?? new CoreTruth();
        $this->worldPhase = $worldPhase ?? WorldPhase::PRIMORDIAL;
        $this->lifeState = $lifeState ?? LifeState::primordial();
    }

    public function getCosmicState(): CosmicState
    {
        return $this->cosmicState;
    }

    public function getEnvironmentState(): EnvironmentState
    {
        return $this->envState;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getWorldPhase(): WorldPhase
    {
        return $this->worldPhase;
    }

    public function getLifeState(): LifeState
    {
        return $this->lifeState;
    }

    public function updateFromSnapshot(WorldSnapshot $snapshot): void
    {
        // Khi Pipeline chạy xong, nó sẽ trả về 1 snapshot tổng, ta chỉ việc Update các phần thuộc trách nhiệm WorldState
        $this->cosmicState = $snapshot->cosmic;
        $this->envState = $snapshot->environment;
        $this->worldPhase = $snapshot->worldPhase;
        $this->lifeState = $snapshot->lifeState;
        $this->year = $snapshot->year;

        if ($this->cosmicState->entropy >= PresetDescriptor::default()->get('critical_entropy_threshold', 0.85)) {
            $this->record(new EntropyCriticalReached($this->getId(), $this->cosmicState->entropy, new \DateTimeImmutable()));
        }
    }

    public function getCoreTruth(): CoreTruth
    {
        return $this->coreTruth;
    }

    public function addTension(Tension $tension): void
    {
        $this->tensions[] = $tension;
    }

    public function getEntropy(): float
    {
        return $this->cosmicState->entropy;
    }

    public function getContradictionIndex(): float
    {
        // Contradiction grows when entropy and causality tension are high
        return ($this->cosmicState->entropy * 0.6 + ($this->cosmicState->causality / 2.0) * 0.4);
    }

    public function getCoherence(): float
    {
        // Coherence is the opposite of entropy and strain
        return max(0.0, 1.0 - ($this->cosmicState->entropy * 0.7 + $this->cosmicState->strain * 0.3));
    }

    public function getTensions(): array
    {
        return $this->tensions;
    }
}

