<?php

namespace Tuzy\Domain\Narrative\Entity;

readonly class Episode
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
        public array $tags, // e.g. ['war', 'ascension']
        public \DateTimeImmutable $recordedOn
    ) {}
}
