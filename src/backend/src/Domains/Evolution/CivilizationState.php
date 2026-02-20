<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution;

use WorldOS\Domains\Shared\AggregateRoot;
use WorldOS\Domains\Evolution\ValueObjects\CivilizationResidual;
use WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot;

class CivilizationState extends AggregateRoot
{
    private string $worldId;
    private CivilizationResidual $residual;
    private CivilizationSnapshot $snapshot;
    
    // Custom tracking
    private int $population;
    private float $internalPressure;

    public function __construct(
        string $id, 
        string $worldId, 
        ?CivilizationResidual $residual = null,
        ?CivilizationSnapshot $snapshot = null
    ) {
        parent::__construct($id);
        $this->worldId = $worldId;
        $this->residual = $residual ?? new CivilizationResidual();
        $this->snapshot = $snapshot ?? CivilizationSnapshot::defaultObservation(0);
        $this->population = 1000;
        $this->internalPressure = 0.0;
    }

    public function getSnapshot(): CivilizationSnapshot
    {
        return $this->snapshot;
    }

    public function updateFromSnapshot(CivilizationSnapshot $newSnapshot): void
    {
        $this->snapshot = $newSnapshot;
    }

    public function getResidual(): CivilizationResidual
    {
        return $this->residual;
    }

    public function getWorldId(): string
    {
        return $this->worldId;
    }

    public function increasePressure(float $amount, string $traumaType = 'social'): void
    {
        $this->internalPressure += $amount;
        $this->residual->addTrauma($amount, $traumaType);

        if ($this->getTotalStress() >= 1.0) {
            // Can trigger a Civilizational Collapse Event
            $this->internalPressure = 0.0;
            $this->population = max(0, intval($this->population * 0.5)); // 50% population loss
        }
    }

    public function decreasePressure(float $amount): void
    {
        $this->internalPressure -= $amount;
        if ($this->internalPressure < 0.0) {
            $this->internalPressure = 0.0;
        }
        
        $this->residual->decreaseTrauma($amount);
    }

    public function getTotalStress(): float
    {
        return $this->internalPressure + $this->residual->getTotalPressure();
    }

    public function ageEra(): void
    {
        $this->residual->decay();
    }
}

