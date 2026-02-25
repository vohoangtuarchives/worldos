<?php

declare(strict_types=1);

namespace App\Modules\Universe\Dto;

/**
 * DTO for creating a new World.
 */
final readonly class DefineWorldDTO
{
    /**
     * @param string $name        Human-readable world name
     * @param string $presetKey   Preset identifier (e.g. 'medieval_fantasy', 'cyberpunk')
     * @param array<string, float> $lawOverrides  Optional overrides for preset law values
     * @param string|null $originType  Origin type (e.g. 'manual', 'generated', 'forked')
     */
    public function __construct(
        public string $name,
        public string $presetKey,
        public array $lawOverrides = [],
        public ?string $originType = null,
    ) {
    }
}
