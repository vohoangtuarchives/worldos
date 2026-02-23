<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\ValueObject;

final class OntologyConfig
{
    /**
     * @param string $presetKey The seed preset used (e.g., 'standard_fantasy')
     * @param string $originType The type of genesis (e.g., 'cosmic', 'artificial')
     */
    public function __construct(
        private readonly string $presetKey = 'default',
        private readonly string $originType = 'unknown'
    ) {
    }

    public static function create(string $presetKey, string $originType = 'unknown'): self
    {
        return new self($presetKey, $originType);
    }

    public function getPresetKey(): string
    {
        return $this->presetKey;
    }

    public function getOriginType(): string
    {
        return $this->originType;
    }

    public function toArray(): array
    {
        return [
            'preset_key' => $this->presetKey,
            'origin_type' => $this->originType,
        ];
    }
}
