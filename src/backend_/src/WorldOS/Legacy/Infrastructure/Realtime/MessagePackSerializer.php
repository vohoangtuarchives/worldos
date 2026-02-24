<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Realtime;

use MessagePack\MessagePack;
use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\StateVector;

/**
 * MessagePackSerializer - High-efficiency binary serializer for simulation data.
 * Optimized for StateVector dimensional data.
 */
class MessagePackSerializer
{
    /**
     * Serializes a WorldSnapshot into a compact MessagePack binary string.
     * Extracts only numeric data for maximum bandwidth efficiency.
     */
    public function serializeSnapshot(WorldSnapshot $snapshot): string
    {
        $civ = $snapshot->civilization;
        
        $data = [
            'y' => $snapshot->year,
            'p' => $snapshot->worldPhase->value,
            'c' => [
                'e' => $snapshot->cosmic->entropy,
                't' => $snapshot->cosmic->cosmicTension(),
            ],
            'env' => [
                'p' => $snapshot->environment->environmentalPressure(),
            ],
        ];

        if ($civ) {
            // Include core 17-dimensional vector data
            $vector = StateVector::fromSnapshot($civ);
            $data['v'] = array_values($vector->toAssocArray());
            $data['s'] = $civ->stability;
            $data['t'] = $civ->narrativeTension;
        }

        return MessagePack::pack($data);
    }

    /**
     * Serializes a Chronicle Event into a MessagePack binary string.
     */
    public function serializeEvent(array $eventData): string
    {
        return MessagePack::pack($eventData);
    }

    /**
     * Serializes only the delta if needed (Planned for next step optimization)
     */
    public function serializeDelta(array $delta): string
    {
        return MessagePack::pack($delta);
    }
}
