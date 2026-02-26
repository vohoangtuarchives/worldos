<?php

declare(strict_types=1);

namespace App\Infrastructure\Broadcasting;

use Illuminate\Support\Facades\Redis;

/**
 * RedisObserverBroadcaster — Phát luồng trạng thái Universe Real-time.
 *
 * Theo WORLDOS_ARCHITECTURE_MULTIVERSE.md §6 Observer Service:
 *   - Subscribe Redis Streams: `universe:events:{universe_id}`
 *   - Fan-out qua Pub/Sub đến các WebSocket client (Next.js Dashboard)
 *
 * Cấu trúc message gửi lên Redis:
 *   {
 *     "type": "TICK_PROCESSED" | "TICK_REJECTED" | "REGIME_TRANSITION" | "NARRATIVE_EVENT",
 *     "universe_id": "...",
 *     "tick": N,
 *     "payload": {...}
 *   }
 *
 * Không lưu trữ trực tiếp vào DB — chỉ là conduit real-time.
 */
final class RedisObserverBroadcaster
{
    /**
     * Broadcast một message tùy ý lên Redis channel của Universe.
     */
    public function broadcast(string $universeId, string $type, array $payload): void
    {
        $message = json_encode([
            'type'         => $type,
            'universe_id'  => $universeId,
            'occurred_at'  => now()->toIso8601String(),
            'payload'      => $payload,
        ]);

        Redis::publish("universe:events:{$universeId}", $message);
    }

    /**
     * Broadcast trạng thái tick hoàn tất (snapshot hiện tại của Universe).
     */
    public function broadcastTickCompleted(
        string $universeId,
        int    $tick,
        array  $observableState,
        string $snapshotHash,
        string $regime,
        float  $stabilityMargin,
        float  $elapsedMs,
    ): void {
        $this->broadcast($universeId, 'TICK_PROCESSED', [
            'tick'             => $tick,
            'observable_state' => $observableState,
            'snapshot_hash'    => $snapshotHash,
            'regime'           => $regime,
            'stability_margin' => $stabilityMargin,
            'elapsed_ms'       => $elapsedMs,
        ]);
    }

    /**
     * Broadcast khi GovernanceGuard từ chối tick.
     */
    public function broadcastTickRejected(
        string $universeId,
        int    $tick,
        string $regime,
        string $reason,
    ): void {
        $this->broadcast($universeId, 'TICK_REJECTED', [
            'tick'   => $tick,
            'regime' => $regime,
            'reason' => $reason,
        ]);
    }

    /**
     * Broadcast sự kiện chuyển pha Regime.
     */
    public function broadcastRegimeTransition(
        string $universeId,
        int    $tick,
        string $from,
        string $to,
        string $cause,
    ): void {
        $this->broadcast($universeId, 'REGIME_TRANSITION', [
            'tick'  => $tick,
            'from'  => $from,
            'to'    => $to,
            'cause' => $cause,
        ]);
    }

    /**
     * Broadcast sự kiện Narrative (Breakthrough, Fragmentation...).
     */
    public function broadcastNarrativeEvent(
        string $universeId,
        int    $tick,
        string $eventType,
        array  $data,
    ): void {
        $this->broadcast($universeId, 'NARRATIVE_EVENT', [
            'tick'       => $tick,
            'event_type' => $eventType,
            'data'       => $data,
        ]);
    }
}
