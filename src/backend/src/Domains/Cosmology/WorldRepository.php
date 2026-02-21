<?php

namespace WorldOS\Domains\Cosmology;

interface WorldRepository
{
    public function save(World $world): void;
    public function findById(string $id): ?World;
}
