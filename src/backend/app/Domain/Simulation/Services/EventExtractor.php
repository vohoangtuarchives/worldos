<?php

declare(strict_types=1);

namespace App\Domain\Simulation\Services;

use App\Domain\Simulation\Events\TickCompleted;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * EventExtractor — Phát hiện và bắn các sự kiện Narrative từ State Vector.
 *
 * Theo RSCD v1.2 (Appendix_02.md), Narrative Events được trích xuất thuần túy
 * từ toán học (threshold crossing, regime transition), KHÔNG dùng AI trong vòng
 * lặp simulation (AXIOM 7: AI is Meta-Layer only).
 *
 * Đây là Observer của TickCompleted, chạy sau mỗi tick thành công.
 *
 * Core Dimensions (theo thứ tự cố định):
 *   [0] Entropy (E), [1] Order (O), [2] Innovation (I)
 *   [3] Cohesion (C), [4] Inequality (Q), [5] Trauma (T)
 */
final class EventExtractor
{
    // Observable state thresholds (sau khi sigmoid: giá trị ∈ (0,1))
    private const INNOVATION_BREAKTHROUGH = 0.75;
    private const INEQUALITY_ELITE        = 0.80;
    private const COHESION_FRAGMENTED     = 0.30;
    private const ENTROPY_CRITICAL        = 0.85;
    private const TRAUMA_SPIRAL           = 0.75;
    private const ORDER_COLLAPSE          = 0.20;

    public function __construct(private readonly Dispatcher $events) {}

    /**
     * Phân tích State Vector và bắn các sự kiện Narrative tương ứng.
     * Được gọi sau mỗi TickCompleted event thành công.
     *
     * @param string $universeId
     * @param int    $tick
     * @param array  $observable Observable state S(t) = sigmoid(x(t)) — values ∈ (0,1)
     * @param string $regime     Tên Regime hiện tại
     * @return array             Danh sách events được phát hiện
     */
    public function extract(
        string $universeId,
        int    $tick,
        array  $observable,
        string $regime,
    ): array {
        $detectedEvents = [];

        [$entropy, $order, $innovation, $cohesion, $inequality, $trauma] = $observable;

        // --- Threshold Crossing Events (Appendix_02 §1.2) ---
        if ($innovation > self::INNOVATION_BREAKTHROUGH) {
            $detectedEvents[] = $this->emit($universeId, $tick, 'INNOVATION_BREAKTHROUGH', [
                'innovation' => $innovation,
                'message'    => "Breakthrough detected at tick {$tick}",
            ]);
        }

        if ($inequality > self::INEQUALITY_ELITE) {
            $detectedEvents[] = $this->emit($universeId, $tick, 'ELITE_CONSOLIDATION', [
                'inequality' => $inequality,
                'message'    => "Elite consolidation threshold crossed",
            ]);
        }

        if ($cohesion < self::COHESION_FRAGMENTED) {
            $detectedEvents[] = $this->emit($universeId, $tick, 'SOCIAL_FRAGMENTATION', [
                'cohesion' => $cohesion,
                'message'  => "Social fragmentation critical",
            ]);
        }

        if ($entropy > self::ENTROPY_CRITICAL) {
            $detectedEvents[] = $this->emit($universeId, $tick, 'ENTROPY_CRITICAL', [
                'entropy' => $entropy,
                'message' => "Entropy approaching collapse threshold",
            ]);
        }

        if ($trauma > self::TRAUMA_SPIRAL && $inequality > 0.65) {
            $detectedEvents[] = $this->emit($universeId, $tick, 'TRAUMA_INEQUALITY_SPIRAL', [
                'trauma'     => $trauma,
                'inequality' => $inequality,
                'message'    => "Trauma-Inequality feedback loop detected (Polarization risk)",
            ]);
        }

        if ($order < self::ORDER_COLLAPSE) {
            $detectedEvents[] = $this->emit($universeId, $tick, 'ORDER_COLLAPSE_IMMINENT', [
                'order'   => $order,
                'message' => "Order dimension collapsing — collapse risk high",
            ]);
        }

        return $detectedEvents;
    }

    /**
     * Emit a Narrative Event lên Dispatcher.
     */
    private function emit(string $universeId, int $tick, string $type, array $payload): array
    {
        $event = [
            'universe_id' => $universeId,
            'tick'        => $tick,
            'type'        => $type,
            'payload'     => $payload,
            'occurred_at' => now()->toIso8601String(),
        ];

        // Tương lai: dispatch NarrativeEventOccurred($event) để lưu vào DB/Redis
        // $this->events->dispatch(new NarrativeEventOccurred($event));

        return $event;
    }
}
