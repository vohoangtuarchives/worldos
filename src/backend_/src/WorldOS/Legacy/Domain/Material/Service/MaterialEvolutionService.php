<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Material\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CosmicState;
use WorldOS\Legacy\Domain\Material\MaterialRegistry;

/**
 * MaterialEvolutionService
 *
 * Đồng bộ hóa trạng thái của Trường (Field) xuống các thực thể vật chất (Factions, Characters).
 * Thực thi các luật "Phản ứng vật chất" dựa trên vĩ mô.
 */
class MaterialEvolutionService
{
    public function __construct(
        private MaterialRegistry $registry
    ) {}

    /**
     * Đồng bộ hóa toàn bộ tầng vật chất theo bước tiến hóa của thế giới.
     */
    public function sync(CivilizationSnapshot $civ, CosmicState $cosmic): void
    {
        $this->updateFactions($civ);
        $this->updateCharacters($civ, $cosmic);
    }

    private function updateFactions(CivilizationSnapshot $civ): void
    {
        foreach ($this->registry->getAllFactions() as $faction) {
            // 1. Power Drift: Prosperity, Military, Expansionism support growth. Entropy causes decay.
            $growth = ($civ->prosperity * 0.06) 
                    + ($civ->militaryPressure * 0.04) 
                    + ($civ->expansionism * 0.02)
                    - ($civ->internalEntropy * 0.05) 
                    - 0.005; // Base decay much smaller now
            
            $faction->modifyPower($growth);

            // 2. Ideology Mutation: High inequality forces radicalization
            if ($civ->inequality > 0.7 || $civ->internalEntropy > 0.8) {
                $faction->mutateIdeology(0.1);
            }

            // 3. Memory Decay
            $faction->decayMemory(0.02);
        }
    }

    private function updateCharacters(CivilizationSnapshot $civ, CosmicState $cosmic): void
    {
        foreach ($this->registry->getAllCharacters() as $character) {
            if (!$character->isAlive()) continue;

            // 1. Survival Check: Based on Civ Stability & Social Entropy
            // We use the existing calculateSurvivalProbability logic but
            // the pipeline will use these results to trigger death if needed.

            // 2. If Stability is 0, characters have a high risk of death
            if ($civ->stability < 0.1 && rand(0, 100) < 5) {
                $character->die();
            }
        }
    }

    public function getTotalFactionPower(): float
    {
        $total = 0.0;
        foreach ($this->registry->getAllFactions() as $faction) {
            $total += $faction->getPowerLevel();
        }
        return $total;
    }

    public function wipeAllMemories(): void
    {
        foreach ($this->registry->getAllFactions() as $faction) {
            $faction->resetMemory();
        }
    }
}
