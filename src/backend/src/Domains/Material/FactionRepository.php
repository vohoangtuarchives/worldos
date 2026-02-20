<?php

namespace WorldOS\Domains\Material;

interface FactionRepository
{
    public function save(Faction $faction): void;
    public function findById(string $id): ?Faction;
}
