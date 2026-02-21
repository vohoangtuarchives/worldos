<?php

namespace Tuzy\Application\Saga\Actions;

use Tuzy\Application\Runtime\Evaluation\MetricsExtractor;
use Tuzy\Application\Runtime\Evaluation\UniverseEvaluatorInterface;
use Tuzy\Domain\Runtime\ValueObject\EvaluationResult;
use App\Models\UniverseModel;

class EvaluateUniverseAction
{
    public function __construct(
        private MetricsExtractor $metricsExtractor,
        private UniverseEvaluatorInterface $evaluator
    ) {}

    public function execute(string $universeId): EvaluationResult
    {
        $metrics = $this->metricsExtractor->fromLatestSnapshot($universeId);
        if ($metrics === null) {
            throw new \RuntimeException("Could not extract metrics for universe {$universeId}. Ensure at least one snapshot exists.");
        }

        return $this->evaluator->evaluate($metrics);
    }
}
