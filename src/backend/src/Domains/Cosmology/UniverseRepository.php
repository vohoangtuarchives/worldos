<?php

namespace WorldOS\Domains\Cosmology;

interface UniverseRepository
{
    public function save(Universe $universe): void;
    public function findById(string $id): ?Universe;
}
