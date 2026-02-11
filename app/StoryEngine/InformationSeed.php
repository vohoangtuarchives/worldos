<?php

namespace App\StoryEngine;

class InformationSeed extends Seed
{
    public float $truthfulness = 1.0; // 0.0 = fake, 1.0 = true
    public string $sourceFactionId;

    public function __construct(
        string $type, 
        string $dimension, 
        int $severity, 
        float $truthfulness = 1.0, 
        string $sourceFactionId = ''
    ) {
        parent::__construct($type, $dimension, $severity);
        $this->truthfulness = $truthfulness;
        $this->sourceFactionId = $sourceFactionId;
    }
}
