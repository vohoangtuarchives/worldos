<?php

namespace WorldOS\Legacy\Domain\Replay;

class DeterministicRandom
{
    private string $seedHash;
    private int $callCounter = 0;

    /**
     * @param string|int $seed The unique seed for this world/session
     */
    public function __construct($seed)
    {
        // Convert any seed to a consistent integer/hash base
        $this->seedHash = hash('sha256', (string)$seed);
    }

    /**
     * Get next random float between 0.0 and 1.0
     */
    public function nextFloat(): float
    {
        $this->callCounter++;
        // Combine seed + counter to get unique output per call
        $hash = hash('sha256', $this->seedHash . '_' . $this->callCounter);
        
        // Take first 8 chars for int
        $intVal = hexdec(substr($hash, 0, 8));
        
        // 0xFFFFFFFF is 4294967295
        return $intVal / 4294967295;
    }

    /**
     * Get random integer between min and max (inclusive)
     */
    public function nextInt(int $min, int $max): int
    {
        $range = $max - $min + 1;
        $float = $this->nextFloat();
        return $min + (int)floor($float * $range);
    }
}
