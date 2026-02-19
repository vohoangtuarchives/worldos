<?php

namespace App\Domains\Cosmology\ValueObjects;

use App\Domains\Cosmology\Enums\SocialClassType;
use InvalidArgumentException;

class SocialClass
{
    public function __construct(
        public readonly SocialClassType $type,
        public readonly float $power,       // 0.0 to 1.0
        public readonly float $contentment, // 0.0 to 1.0
        public readonly float $size,        // 0.0 to 1.0
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->power < 0.0 || $this->power > 1.0) {
            throw new InvalidArgumentException("Power range error: {$this->power}");
        }
        if ($this->contentment < 0.0 || $this->contentment > 1.0) {
            throw new InvalidArgumentException("Contentment range error: {$this->contentment}");
        }
        if ($this->size < 0.0 || $this->size > 1.0) {
            throw new InvalidArgumentException("Size range error: {$this->size}");
        }
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'power' => $this->power,
            'contentment' => $this->contentment,
            'size' => $this->size,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            SocialClassType::from($data['type']),
            (float) $data['power'],
            (float) $data['contentment'],
            (float) $data['size']
        );
    }
}
