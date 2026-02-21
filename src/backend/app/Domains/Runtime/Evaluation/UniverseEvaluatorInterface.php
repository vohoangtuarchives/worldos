<?php

namespace App\Domains\Runtime\Evaluation;

use Tuzy\Domain\Runtime\ValueObject\EvaluationResult;
use Tuzy\Domain\Runtime\ValueObject\UniverseMetrics;

interface UniverseEvaluatorInterface
{
    public function evaluate(UniverseMetrics $metrics): EvaluationResult;
}
