<?php

declare(strict_types=1);

namespace App\WorldOS\Saga\Dto;

/**
 * DTO for creating a new Saga experiment.
 */
final readonly class CreateSagaDTO
{
    public function __construct(
        public string $name,
        public ?string $presetKey = null,
    ) {
    }
}
