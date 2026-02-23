<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\ValueObject;

use WorldOS\Legacy\Domain\Cosmology\Enums\SocialClassType;
use InvalidArgumentException;

final readonly class SocialClass
{
    public function __construct(
        public SocialClassType $type,
        public float $power,
        public float $contentment,
        public float $size,
    ) {
        if ($power < 0.0 || $power > 1.0) {
            throw new InvalidArgumentException("Power range error: {$power}");
        }
        if ($contentment < 0.0 || $contentment > 1.0) {
            throw new InvalidArgumentException("Contentment range error: {$contentment}");
        }
        if ($size < 0.0 || $size > 1.0) {
            throw new InvalidArgumentException("Size range error: {$size}");
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
