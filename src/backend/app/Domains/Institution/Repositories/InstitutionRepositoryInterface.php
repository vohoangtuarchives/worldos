<?php

namespace App\Domains\Institution\Repositories;

use App\Models\Institution;
use Illuminate\Support\Collection;

interface InstitutionRepositoryInterface
{
    public function findAllForWorld(string $worldId): Collection;
    public function findById(string $id): ?Institution;
    public function save(Institution $institution): void;
}
