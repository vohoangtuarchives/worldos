<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\DeleteUniverse;

final readonly class DeleteUniverseCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
