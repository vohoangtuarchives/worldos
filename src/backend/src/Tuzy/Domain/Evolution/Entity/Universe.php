<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Entity;

use Tuzy\Domain\Shared\Entity\AggregateRoot;
use Tuzy\Domain\Evolution\Entity\LawGenome;
use Tuzy\Domain\Evolution\ValueObject\CosmicState;
use Tuzy\Domain\Evolution\ValueObject\EnvironmentState;
use Tuzy\Domain\Evolution\Enum\WorldPhase;
use Tuzy\Domain\Evolution\ValueObject\LifeState;

class Universe extends AggregateRoot
{
    private LawGenome $lawGenome;
    private CosmicState $cosmicState;
    private EnvironmentState $envState;
    private int $year;
    private WorldPhase $phase;
    private LifeState $lifeState;

    public function __construct(
        string $id,
        LawGenome $lawGenome,
        ?CosmicState $cosmicState = null,
        ?EnvironmentState $envState = null,
        int $year = 0,
        ?WorldPhase $phase = null,
        ?LifeState $lifeState = null
    ) {
        parent::__construct($id);
        $this->lawGenome = $lawGenome;
        $this->cosmicState = $cosmicState ?? CosmicState::defaultObservation($year);
        $this->envState = $envState ?? EnvironmentState::defaultObservation($year);
        $this->year = $year;
        $this->phase = $phase ?? WorldPhase::PRIMORDIAL;
        $this->lifeState = $lifeState ?? LifeState::primordial();
    }

    public function getLawGenome(): LawGenome
    {
        return $this->lawGenome;
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

    public function getPhase(): WorldPhase
    {
        return $this->phase;
    }

    public function getLifeState(): LifeState
    {
        return $this->lifeState;
    }
    
    public function incrementYear(int $delta = 1): void
    {
        $this->year += $delta;
    }
}
