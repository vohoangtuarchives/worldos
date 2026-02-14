<?php

namespace App\Domains\Saga\Dto;

class StoryMetadataDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly array $tags, // Vietnamese tags
        public readonly string $primaryGenre,
        public readonly array $chapters = []
    ) {}

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
