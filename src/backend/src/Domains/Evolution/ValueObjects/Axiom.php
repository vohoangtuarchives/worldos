<?php

namespace WorldOS\Domains\Evolution\ValueObjects;

use InvalidArgumentException;

class Axiom
{
    public function __construct(
        public readonly string $id,
        public readonly string $description,
        public readonly bool $isAbsolute = true
    ) {
        if (empty($id) || empty($description)) {
            throw new InvalidArgumentException("Axiom requires valid id and description.");
        }
    }
}


