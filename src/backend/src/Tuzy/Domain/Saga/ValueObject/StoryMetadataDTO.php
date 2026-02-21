<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga\ValueObject;

final readonly class StoryMetadataDTO
{
    public function __construct(
        public string $title,
        public string $description,
        public array $tags,
        public string $primaryGenre,
        public array $chapters = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? 'Truyện chưa đặt tên',
            description: $data['description'] ?? 'Chưa có mô tả.',
            tags: $data['tags'] ?? [],
            primaryGenre: $data['primary_genre'] ?? 'Chưa rõ',
            chapters: $data['chapters'] ?? []
        );
    }
}
