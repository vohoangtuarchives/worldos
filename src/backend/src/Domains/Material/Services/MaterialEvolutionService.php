<?php

declare(strict_types=1);

namespace WorldOS\Domains\Material\Services;

use WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot;
use WorldOS\Domains\Evolution\ValueObjects\CosmicState;
use WorldOS\Domains\Material\MaterialRegistry;

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
            // 1. Power Drift: Prosperity supports growth, Stability prevents decay
            $growth = ($civ->prosperity * 0.05) + ($civ->stability * 0.02) - 0.03;
            $faction->modifyPower($growth);

            // 2. Ideology Mutation: High inequality forces radicalization
            if ($civ->inequality > 0.7) {
                $faction->mutateIdeology(0.1);
            }

            // 3. Memory Decay
            $faction->decayMemory(0.05);
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
