<?php

namespace App\Domains\Runtime\Evaluation;

interface UniverseEvaluatorInterface
{
    public function evaluate(UniverseMetrics $metrics): EvaluationResult;
}
