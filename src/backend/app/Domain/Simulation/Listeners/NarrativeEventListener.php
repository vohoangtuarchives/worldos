<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Listeners;

use App\Domain\Simulation\Events\TickCompleted;
use App\Domain\Simulation\Regimes\RegimeResolver;
use App\Domain\Simulation\Services\EventExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * NarrativeEventListener — Lắng nghe TickCompleted và xử lý hậu kỳ.
 *
 * Sau mỗi Tick thành công:
 *   1. Tính Observable State S(t) = sigmoid(x(t))
 *   2. Phát hiện Narrative Events (threshold crossing) qua EventExtractor
 *   3. Phát hiện chuyển pha Regime (R1→R2→...) qua RegimeResolver
 *   4. Broadcast tất cả kết quả qua Redis → WebSocket Observer
 *
 * Chạy async qua Queue (ShouldQueue) để không block vòng lặp simulation.
 */
final class NarrativeEventListener implements ShouldQueue
{
    public string $queue = 'narrative';

    public function __construct(
        private readonly EventExtractor  $extractor,
        private readonly RegimeResolver  $resolver,
    ) {}

    /**
     * Phản ứng với TickCompleted event.
     */
    public function handle(TickCompleted $event): void
    {
        // 1. Chuyển đổi Latent State → Observable State (sigmoid)
        $observable = array_map(fn ($xi) => 1.0 / (1.0 + exp(-$xi)), $event->nextState);

        // 2. Phát hiện Narrative Events
        $narrativeEvents = $this->extractor->extract(
            $event->universeId,
            $event->tick,
            $observable,
            $event->regime,
        );

        // 3. Phát hiện chuyển pha Regime
        $transition = $this->resolver->detectTransition($observable, $event->regime);
        $stabilityMargin = $this->resolver->estimateStabilityMargin($event->regime);

        // 4. Broadcast ra Redis channel cho Observer/WebSocket
        $payload = json_encode([
            'type'             => 'TICK_PROCESSED',
            'universe_id'      => $event->universeId,
            'experiment_id'    => $event->experimentId,
            'tick'             => $event->tick,
            'observable_state' => $observable,
            'snapshot_hash'    => $event->nextHash,
            'regime'           => $event->regime,
            'stability_margin' => $stabilityMargin,
            'regime_transition' => $transition['transitioned'] ? $transition : null,
            'narrative_events' => $narrativeEvents,
            'elapsed_ms'       => $event->elapsedMs,
        ]);

        Redis::publish("universe:events:{$event->universeId}", $payload);

        // Log regime transition
        if ($transition['transitioned']) {
            Log::info("[WorldOS] Regime transition on Universe {$event->universeId}", [
                'tick'  => $event->tick,
                'from'  => $transition['from'],
                'to'    => $transition['to'],
                'cause' => $transition['cause'],
            ]);
        }

        // Log narrative events nếu có
        foreach ($narrativeEvents as $ne) {
            Log::info("[WorldOS] Narrative Event fired", [
                'universe_id' => $event->universeId,
                'tick'        => $event->tick,
                'type'        => $ne['type'],
            ]);
        }
    }
}
