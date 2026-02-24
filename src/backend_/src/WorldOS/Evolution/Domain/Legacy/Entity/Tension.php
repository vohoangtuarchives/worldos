<?php

namespace WorldOS\Evolution\Domain\Legacy\Entity;

readonly class Tension
{
    public function __construct(
        public string $domain, // e.g., 'civilization', 'energy', 'supernatural'
        public float $level,   // 0.0 to 1.0
        public string $source  // e.g., 'famine', 'god_miracle'
    ) {}
}

