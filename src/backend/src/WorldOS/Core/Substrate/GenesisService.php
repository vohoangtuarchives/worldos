<?php

declare(strict_types=1);

namespace WorldOS\Core\Substrate;

use Illuminate\Support\Facades\Storage;

/**
 * GenesisService: Responsible for the birth of a Universe and "Sealing" the Substrate Law.
 */
class GenesisService
{
    private const STORAGE_PATH = 'worldos/substrate.lock';

    /**
     * Initializes a new Universe with a sealed substrate.
     * The original seed is destroyed immediately after hashing.
     */
    public function sealUniverse(): void
    {
        if (Storage::disk('local')->exists(self::STORAGE_PATH)) {
            // Universe is already sealed.
            return;
        }

        // 1. Generate high-entropy seed
        $seed = random_bytes(64);

        // 2. Hash the seed to create the Substrate Key
        $hash = hash('sha512', $seed);

        // 3. Persist ONLY the hash
        Storage::disk('local')->put(self::STORAGE_PATH, $hash);

        // 4. Force-clear the seed from memory (as much as PHP allows)
        $seed = str_repeat("\0", 64);
        unset($seed);
    }

    public function getSubstrateHash(): ?string
    {
        if (!Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return null;
        }

        return Storage::disk('local')->get(self::STORAGE_PATH);
    }
}
