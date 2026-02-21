<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Realtime;

use Illuminate\Support\Facades\Redis;
use Tuzy\Domain\Evolution\ValueObject\WorldSnapshot;

/**
 * RealtimeStreamServer - Manages the distribution of simulation data.
 * Uses Redis Streams as a high-performance message bus.
 */
class RealtimeStreamServer
{
    private const STREAM_KEY_PREFIX = 'world_stream:';
    private const MAX_STREAM_ENTRIES = 100; // Keep only last 100 ticks per world

    public function __construct(
        private MessagePackSerializer $serializer
    ) {}

    /**
     * Pushes a global update for all active worlds.
     */
    public function broadcastUpdate(WorldSnapshot $snapshot, string $worldId): void
    {
        $binaryData = $this->serializer->serializeSnapshot($snapshot);
        $streamKey = self::STREAM_KEY_PREFIX . $worldId;

        try {
            // Fix: phpredis XADD usually expects key, id, [fields]
            // Using raw command requires careful argument mapping or just use direct method
            Redis::xadd(
                $streamKey,
                '*',
                ['type' => 'metric', 'p' => $binaryData],
                self::MAX_STREAM_ENTRIES,
                true // Approximate maxlen
            );

            // Also publish to a simple pub/sub channel for instant notification
            // if SSE servers are listening live.
            Redis::publish('world_updates_channel', json_encode([
                'world_id' => $worldId,
                'year' => $snapshot->year
            ]));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("RealtimeStream Error: " . $e->getMessage());
        }
    }

    /**
     * Pushes a chronicle event to the stream.
     */
    public function broadcastEvent(array $eventData, string $worldId): void
    {
        $binaryData = $this->serializer->serializeEvent($eventData);
        $streamKey = self::STREAM_KEY_PREFIX . $worldId;

        try {
            Redis::xadd(
                $streamKey,
                '*',
                ['type' => 'chronicle', 'p' => $binaryData],
                self::MAX_STREAM_ENTRIES,
                true
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("RealtimeStream Event Error: " . $e->getMessage());
        }
    }

    /**
     * Gets the latest state from the stream key.
     */
    public function getLatestBinary(string $worldId): ?string
    {
        $streamKey = self::STREAM_KEY_PREFIX . $worldId;
        
        // XREVRANGE key + - COUNT 20
        $entries = Redis::xrevrange($streamKey, '+', '-', 20);
        
        if (empty($entries)) {
            return null;
        }

        // Find the latest entry that is a metric
        foreach ($entries as $fields) {
            $type = $fields['type'] ?? 'metric';
            if ($type === 'metric') {
                return $fields['p'] ?? null;
            }
        }
        
        return null;
    }
}
