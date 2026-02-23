<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Infrastructure\Persistence;

use App\Models\ChronicleEventModel;
use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Chronicle\Domain\Repository\ChronicleRepositoryInterface;
use WorldOS\Chronicle\Domain\ValueObject\ChronicleEventId;
use WorldOS\Chronicle\Domain\ValueObject\EventType;
use WorldOS\Chronicle\Domain\ValueObject\Severity;

final class EloquentChronicleRepository implements ChronicleRepositoryInterface
{
    public function save(ChronicleEvent $event): void
    {
        ChronicleEventModel::create([
            'id'          => $event->getId()->toString(),
            'universe_id' => $event->getUniverseId(),
            'tick'        => $event->getTick(),
            'seed'        => $event->getSeed(),
            'type'        => $event->getType()->value,
            'title'       => $event->getTitle(),
            'severity'    => $event->getSeverity()->value,
            'payload'     => $event->getPayload(),
            'occurred_at' => $event->getOccurredAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findByUniverse(string $universeId, int $limit = 50, int $offset = 0): array
    {
        return ChronicleEventModel::where('universe_id', $universeId)
            ->orderByDesc('tick')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn($m) => $this->hydrate($m))
            ->all();
    }

    public function findByUniverseAndTick(string $universeId, int $tick): array
    {
        return ChronicleEventModel::where('universe_id', $universeId)
            ->where('tick', $tick)
            ->get()
            ->map(fn($m) => $this->hydrate($m))
            ->all();
    }

    public function findByType(string $universeId, EventType $type, int $limit = 50): array
    {
        return ChronicleEventModel::where('universe_id', $universeId)
            ->where('type', $type->value)
            ->orderByDesc('tick')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->hydrate($m))
            ->all();
    }

    public function countByUniverse(string $universeId): int
    {
        return ChronicleEventModel::where('universe_id', $universeId)->count();
    }

    private function hydrate(ChronicleEventModel $model): ChronicleEvent
    {
        return ChronicleEvent::record(
            universeId: $model->universe_id,
            tick:       $model->tick,
            seed:       $model->seed,
            type:       EventType::from($model->type),
            title:      $model->title,
            severity:   Severity::from($model->severity),
            payload:    $model->payload ?? []
        );
    }
}
