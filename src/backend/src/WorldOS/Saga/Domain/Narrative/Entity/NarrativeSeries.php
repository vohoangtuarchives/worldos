<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Narrative\Entity;

final class NarrativeSeries
{
    private function __construct(
        private string $id,
        private string $title,
    ) {
    }

    public static function create(string $title, ?string $id = null): self
    {
        return new self(
            $id ?? self::generateId(),
            $title,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    private static function generateId(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
