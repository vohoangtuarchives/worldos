<?php

namespace Tuzy\Infrastructure\History\Repositories;

use App\Models\Scar;
use Illuminate\Support\Collection;

interface ScarRepositoryInterface
{
    public function findActiveScarsForWorld(string $worldId): Collection;
    public function findActiveScarsForFaction(string $worldId, string $factionId): Collection;
    public function save(Scar $scar): void;
}
