<?php

namespace App\StoryEngine;

class FactionMemory
{
    public string $factionId;

    /** @var array<string, int> */
    public array $successCounter = [];

    /** @var array<string, int> */
    public array $failureCounter = [];

    public array $traumaTags = []; // e.g., 'total_war_loss'

    public function __construct(string $factionId)
    {
        $this->factionId = $factionId;
    }
}
