<?php

namespace Tuzy\Domain\World\Contracts;

interface WorldMetricCalculator
{
    public function calculate(array $snapshot): array;
}
