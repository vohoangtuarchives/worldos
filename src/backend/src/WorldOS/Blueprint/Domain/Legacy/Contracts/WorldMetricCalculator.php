<?php

namespace WorldOS\Blueprint\Domain\Legacy\Contracts;

interface WorldMetricCalculator
{
    public function calculate(array $snapshot): array;
}
