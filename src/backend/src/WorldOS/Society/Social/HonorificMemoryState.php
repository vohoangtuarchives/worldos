<?php

namespace WorldOS\Society\Social;

class HonorificMemoryState
{
    public function __construct(
        public string $currentHonorific,
        public int $stability = 0 // How "stuck" this honorific is. Higher = harder to change.
    ) {}
}
