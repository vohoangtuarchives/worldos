<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Entity;

use WorldOS\Legacy\Domain\Shared\Entity\AggregateRoot;
use WorldOS\Evolution\Domain\Legacy\Entity\LawGenome;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CosmicState;
use WorldOS\Evolution\Domain\Legacy\ValueObject\EnvironmentState;
use WorldOS\Evolution\Domain\Legacy\Enum\WorldPhase;
use WorldOS\Evolution\Domain\Legacy\ValueObject\LifeState;

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
