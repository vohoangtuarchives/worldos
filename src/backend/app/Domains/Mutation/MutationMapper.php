<?php

declare(strict_types=1);

namespace App\Domains\Mutation;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Narrative\Planning\ArcType;
use WorldOS\Saga\Domain\Narrative\ValueObject\StoryOutcomeDTO;

/**
 * Maps StoryOutcomeDTO + ArcType to a structural delta (WorldStateVector).
 * Templates per outcome type (e.g. rebellion_success → decrease order, increase cohesion).
 */
class MutationMapper
{
    /**
     * Returns delta vector (additive). Apply magnitude multiplier (1.0 or shadow 0.3) externally.
     */
    public function mapToDelta(StoryOutcomeDTO $outcome, ?ArcType $arcType): WorldStateVector
    {
        $base = $this->templateFor($outcome->result, $arcType);
        $multiplied = $base->multiply($outcome->intensity);
        return WorldStateVector::fromArray($multiplied->getAll());
    }

    private function templateFor(string $result, ?ArcType $arcType): WorldStateVector
    {
        $arc = $arcType ?? ArcType::REBELLION;

        return match ($arc) {
            ArcType::REBELLION => $this->rebellionTemplate($result),
            ArcType::RISE_AND_FALL => $this->riseAndFallTemplate($result),
            ArcType::POWER_CONSOLIDATION => $this->powerConsolidationTemplate($result),
        };
    }

    private function rebellionTemplate(string $result): WorldStateVector
    {
        return match ($result) {
            StoryOutcomeDTO::RESULT_WIN => WorldStateVector::create(
                -0.08, 0.04, 0.05, -0.06, 0.02, -0.02,
                -0.04, 0.02, -0.04, 0.0
            ),
            StoryOutcomeDTO::RESULT_LOSE => WorldStateVector::create(
                0.06, -0.02, -0.04, 0.05, -0.02, 0.03,
                0.03, 0.04, 0.04, 0.0
            ),
            default => WorldStateVector::create(
                -0.03, 0.02, 0.02, -0.02, 0.0, 0.0,
                -0.02, 0.01, -0.02, 0.0
            ),
        };
    }

    private function riseAndFallTemplate(string $result): WorldStateVector
    {
        return match ($result) {
            StoryOutcomeDTO::RESULT_WIN => WorldStateVector::create(
                -0.04, 0.06, -0.02, -0.03, 0.04, 0.0,
                0.02, 0.03, -0.03, 0.0
            ),
            StoryOutcomeDTO::RESULT_LOSE => WorldStateVector::create(
                0.04, -0.03, 0.03, 0.04, -0.02, 0.02,
                -0.02, -0.02, 0.04, 0.0
            ),
            default => WorldStateVector::create(
                0.0, 0.02, 0.0, 0.0, 0.01, 0.0,
                0.0, 0.01, 0.0, 0.0
            ),
        };
    }

    private function powerConsolidationTemplate(string $result): WorldStateVector
    {
        return match ($result) {
            StoryOutcomeDTO::RESULT_WIN => WorldStateVector::create(
                0.06, -0.04, -0.02, 0.04, -0.02, 0.04,
                0.04, 0.0, 0.08, 0.0
            ),
            StoryOutcomeDTO::RESULT_LOSE => WorldStateVector::create(
                -0.05, 0.04, 0.04, -0.05, 0.02, -0.02,
                -0.03, 0.02, -0.06, 0.0
            ),
            default => WorldStateVector::create(
                0.02, -0.01, -0.01, 0.02, 0.0, 0.01,
                0.01, 0.0, 0.02, 0.0
            ),
        };
    }
}
