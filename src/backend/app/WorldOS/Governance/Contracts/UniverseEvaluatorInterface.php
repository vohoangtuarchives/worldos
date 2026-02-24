<?php

declare(strict_types=1);

namespace App\WorldOS\Governance\Contracts;

use App\WorldOS\Governance\ValueObjects\EvaluationResult;
use App\WorldOS\Governance\ValueObjects\UniverseMetrics;

/**
 * Universe Evaluator Contract.
 *
 * From docs §13.2: UniverseEvaluator → continuing/forking/archiving.
 *
 * Implementations: RuleBasedEvaluator (deterministic), future LLMEvaluator.
 */
interface UniverseEvaluatorInterface
{
    public function evaluate(UniverseMetrics $metrics): EvaluationResult;
}
