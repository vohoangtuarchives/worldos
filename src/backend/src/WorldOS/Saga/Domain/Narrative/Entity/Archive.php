<?php

namespace WorldOS\Saga\Domain\Narrative\Entity;

use WorldOS\Legacy\Domain\Shared\Entity\AggregateRoot;

class Archive extends AggregateRoot
{
    private string $worldId;
    /** @var Episode[] */
    private array $episodes = [];
    private float $epistemicInstability;

    public function __construct(string $id, string $worldId)
    {
        parent::__construct($id);
        $this->worldId = $worldId;
        $this->epistemicInstability = 0.0;
    }

    public function recordEpisode(Episode $episode): void
    {
        $this->episodes[] = $episode;
    }

    public function increaseInstability(float $amount): void
    {
        $this->epistemicInstability += $amount;
        if ($this->epistemicInstability >= 1.0) {
            $this->epistemicInstability = 1.0;
            // Optionally trigger EpistemicRippleOccurred
        }
    }

    public function getEpistemicInstability(): float
    {
        return $this->epistemicInstability;
    }

    /**
     * @return Episode[]
     */
    public function getRecentEpisodes(int $limit = 5): array
    {
        $count = count($this->episodes);
        if ($count === 0) {
            return [];
        }
        return array_slice($this->episodes, max(0, $count - $limit));
    }
}
