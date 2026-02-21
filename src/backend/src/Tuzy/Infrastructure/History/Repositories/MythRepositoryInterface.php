<?php

namespace Tuzy\Infrastructure\History\Repositories;

use App\Models\Myth;
use App\Models\MythVersion;
use Illuminate\Support\Collection;

interface MythRepositoryInterface
{
    public function findActiveMythsForWorld(string $worldId): Collection;
    public function save(Myth $myth): void;
    public function saveVersion(MythVersion $version): void;
    public function findById(string $id): ?Myth;
}
