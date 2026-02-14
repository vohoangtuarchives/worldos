<?php

namespace App\Domains\Cosmic\Services;

use App\Domains\Cosmic\Models\Attractor;
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
