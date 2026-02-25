<?php

declare(strict_types=1);

namespace App\Infrastructure\Kernel;

use App\Domain\Kernel\MathCore;

/**
 * Service to manage the immutable SHA256 integrity hashing 
 * of consecutive state snapshots.
 */
final class HashChainService
{
    private MathCore $mathCore;

    public function __construct(MathCore $mathCore)
    {
        $this->mathCore = $mathCore;
    }

    /**
     * Compute the next hash in the chain given the previous hash
     * and the new latent state vector.
     *
     * @param string $previousHash
     * @param array<int, float> $currentState
     * @return string
     */
    public function generateNextHash(string $previousHash, array $currentState): string
    {
        return $this->mathCore->snapshotHash($previousHash, $currentState);
    }

    /**
     * Creates a genesis hash from an initial state vector.
     *
     * @param array<int, float> $initialState
     * @return string
     */
    public function generateGenesisHash(array $initialState): string
    {
        return $this->generateNextHash('0', $initialState);
    }
}
