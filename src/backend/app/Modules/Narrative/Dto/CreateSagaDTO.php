<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Dto;

/**
 * DTO for creating a new Saga experiment.
 */
final readonly class CreateSagaDTO
{
    /**
     * @param string[] $universeIds Optional array of universe IDs to attach to the Saga.
     */
    public function __construct(
        public string $name,
        public ?string $presetKey = null,
        public array $universeIds = [],
    ) {
    }
}
