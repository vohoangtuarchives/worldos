<?php

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\ValueObjects\Attractor;
use App\Domains\Cosmology\ValueObjects\CosmicState;
use Illuminate\Database\Eloquent\Collection;

class AttractorService
{
    /**
     * Retrieve an attractor by its unique code
     */
    public function getByCode(string $code): ?Attractor
    {
        return Attractor::where('code', $code)->first();
    }

    /**
     * Get all active attractors
     */
    public function getAllActive(): Collection
    {
        return Attractor::all();
    }
}
