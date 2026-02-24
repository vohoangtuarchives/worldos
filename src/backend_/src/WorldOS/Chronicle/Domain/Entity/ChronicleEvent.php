<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\Entity;

use WorldOS\Chronicle\Domain\ValueObject\ChronicleEventId;
use WorldOS\Chronicle\Domain\ValueObject\EventType;
use WorldOS\Chronicle\Domain\ValueObject\Severity;

/**
 * ChronicleEvent — Records a significant narrative moment in a Universe's simulation history.
 * Immutable once created. Acts as the permanent history ledger of the Simulation.
 */
final class ChronicleEvent
{
    private function __construct(
        private readonly ChronicleEventId $id,
        private readonly string           $universeId,
        private readonly int              $tick,
        private readonly int              $seed,
        private readonly EventType        $type,
        private readonly string           $title,
        private readonly Severity         $severity,
        private readonly array            $payload,
        private readonly \DateTimeImmutable $occurredAt
    ) {
    }

    public static function record(
        string    $universeId,
        int       $tick,
        int       $seed,
        EventType $type,
        string    $title,
        Severity  $severity,
        array     $payload = []
    ): self {
        return new self(
            id:          ChronicleEventId::generate(),
            universeId:  $universeId,
            tick:        $tick,
            seed:        $seed,
            type:        $type,
            title:       $title,
            severity:    $severity,
            payload:     $payload,
            occurredAt:  new \DateTimeImmutable()
        );
    }

    public function getId(): ChronicleEventId     { return $this->id; }
    public function getUniverseId(): string        { return $this->universeId; }
    public function getTick(): int                 { return $this->tick; }
    public function getSeed(): int                 { return $this->seed; }
    public function getType(): EventType           { return $this->type; }
    public function getTitle(): string             { return $this->title; }
    public function getSeverity(): Severity        { return $this->severity; }
    public function getPayload(): array            { return $this->payload; }
    public function getOccurredAt(): \DateTimeImmutable { return $this->occurredAt; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id->toString(),
            'universe_id' => $this->universeId,
            'tick'        => $this->tick,
            'seed'        => $this->seed,
            'type'        => $this->type->value,
            'title'       => $this->title,
            'severity'    => $this->severity->value,
            'payload'     => $this->payload,
            'occurred_at' => $this->occurredAt->format(\DATE_ATOM),
        ];
    }
}
