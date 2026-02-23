<?php

namespace WorldOS\Legacy\Application\Runtime\Evaluation;

use WorldOS\Legacy\Domain\Runtime\ValueObject\EvaluationResult;
use WorldOS\Legacy\Domain\Runtime\ValueObject\UniverseMetrics;

interface UniverseEvaluatorInterface
{
    public function evaluate(UniverseMetrics $metrics): EvaluationResult;
}
