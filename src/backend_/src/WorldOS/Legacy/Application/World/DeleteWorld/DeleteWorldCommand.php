<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\DeleteWorld;

final readonly class DeleteWorldCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
