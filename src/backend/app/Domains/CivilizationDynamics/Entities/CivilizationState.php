<?php

namespace App\Domains\CivilizationDynamics\Entities;

use App\Domains\CivilizationDynamics\ValueObjects\CivilizationResidual;
use App\Domains\Cosmology\Entities\WorldStateVector;
use Carbon\Carbon;

/**
 * Mảng trạng thái của một nền văn minh cụ thể.
 * Tồn tại độc lập tương đối với WorldState, nhưng bị bao trùm bởi nó.
 */
class CivilizationState
{
    public function __construct(
        public readonly string $id,
        public readonly string $sagaId,
        public readonly string $name,
        // State hiện tại của nền văn minh (Cũng dùng base vector cho dễ tương tác)
        public WorldStateVector $vector,
        // Ký ức văn minh (Scar/Trauma/Forces)
        public CivilizationResidual $residualMemory,
        public int $foundedYear
    ) {}

    public function age(int $currentYear): int
    {
        return $currentYear - $this->foundedYear;
    }

    public function decayResiduals(int $yearsPassed): void
    {
        $this->residualMemory->decay($yearsPassed);
    }

    public function addTrauma(string $type, float $magnitude): void
    {
        $this->residualMemory->addScars([$type => $magnitude]);
    }
}
