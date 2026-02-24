<?php

namespace WorldOS\Saga\Domain\Narrative\Entity;

use WorldOS\Legacy\Domain\Shared\Entity\AggregateRoot;

class Myth extends AggregateRoot
{
    private string $worldId;
    private string $title;
    private string $content;
    private float $believability; // Thể hiện độ tin cậy bị xói mòn bởi sương mù nhận thức
    private array $tags = [];

    public function __construct(string $id, string $worldId, string $title, string $content, float $believability = 1.0)
    {
        parent::__construct($id);
        $this->worldId = $worldId;
        $this->title = $title;
        $this->content = $content;
        $this->believability = $believability;
    }

    public function erode(float $amount): void
    {
        $this->believability -= $amount;
        if ($this->believability < 0.0) {
            $this->believability = 0.0;
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getBelievability(): float
    {
        return $this->believability;
    }
}
