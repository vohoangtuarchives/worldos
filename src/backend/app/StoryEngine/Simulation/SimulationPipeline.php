<?php

namespace App\StoryEngine\Simulation;

class SimulationPipeline
{
    /** @var SimulationPhaseInterface[] */
    protected array $phases = [];

    public function addPhase(SimulationPhaseInterface $phase): self
    {
        $this->phases[] = $phase;
        return $this;
    }

    public function run(SimulationContext $context): void
    {
        foreach ($this->phases as $phase) {
            $phase->execute($context);
        }
    }
}
