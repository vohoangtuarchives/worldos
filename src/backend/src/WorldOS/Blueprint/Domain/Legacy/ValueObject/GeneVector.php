<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\ValueObject;

readonly class GeneVector
{
    public function __construct(
        private array $data = [],
    ) {
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public static function martial(): self
    {
        return new self(['preset' => 'martial']);
    }

    public static function immortal(): self
    {
        return new self(['preset' => 'immortal']);
    }

    public static function apocalypse(): self
    {
        return new self(['preset' => 'apocalypse']);
    }

    public static function tech(): self
    {
        return new self(['preset' => 'tech']);
    }

    public static function myth(): self
    {
        return new self(['preset' => 'myth']);
    }
}
