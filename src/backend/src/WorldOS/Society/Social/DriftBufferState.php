<?php

namespace WorldOS\Society\Social;

class DriftBufferState
{
    public function __construct(
        public int $respectPoints = 0,
        public int $hostilityPoints = 0,
        public int $familiarityPoints = 0
    ) {}
}
