<?php

namespace App\Domains\World\Contracts;

interface WorldMetricCalculator
{
    public function calculate(array $snapshot): array;
}
