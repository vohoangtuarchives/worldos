<?php

declare(strict_types=1);

namespace WorldOS\Infrastructure\Persistence\Evolution;

use WorldOS\Domains\Evolution\Contracts\AttractorRepositoryInterface;
use WorldOS\Domains\Evolution\AttractorAggregate;
use WorldOS\Domains\Evolution\ValueObjects\AttractorIncarnation;
use WorldOS\Domains\Evolution\ValueObjects\Attractor;

class InMemoryAttractorRepository implements AttractorRepositoryInterface
{
    private array $attractors = [];
    private array $incarnations = [];

    public function __construct()
    {
        // Khởi tạo sẵn tất cả catalog (EQUILIBRIUM, CHAOS, GOLDEN_AGE, DARK_AGE)
        $catalog = Attractor::catalog();
        foreach ($catalog as $code => $vo) {
            $this->save(AttractorAggregate::fromAttractor($vo, 0));
        }
    }

    public function findByCode(string $code): ?AttractorAggregate
    {
        return $this->attractors[$code] ?? null;
    }

    public function findById(string $id): ?AttractorAggregate
    {
        // Trong trường hợp mẫu này, ID và Code là giống nhau
        return $this->attractors[$id] ?? null;
    }

    public function save(AttractorAggregate $attractor): void
    {
        $this->attractors[$attractor->getCode()] = $attractor;
    }

    public function saveIncarnation(AttractorIncarnation $incarnation): void
    {
        $this->incarnations[$incarnation->id] = $incarnation;
    }

    public function getCurrentIncarnation(string $attractorId): ?AttractorIncarnation
    {
        $attractor = $this->findById($attractorId);
        return $attractor ? $attractor->currentIncarnation() : null;
    }

    public function getIncarnationTree(string $attractorId): array
    {
        $attractor = $this->findById($attractorId);
        return $attractor ? $attractor->getIncarnations() : [];
    }

    public function closeIncarnation(string $incarnationId, int $endTick): void
    {
        if (isset($this->incarnations[$incarnationId])) {
            $inc = $this->incarnations[$incarnationId];
            $inc->endTick = $endTick;
        }
    }
}
