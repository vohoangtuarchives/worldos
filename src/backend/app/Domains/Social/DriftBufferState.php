<?php

namespace App\Domains\Social;

class DriftBufferState
{
    public function __construct(
        public int $respectPoints = 0,
        public int $hostilityPoints = 0,
        public int $familiarityPoints = 0
    ) {}
}
