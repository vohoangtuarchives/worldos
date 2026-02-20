<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot;
use WorldOS\Domains\Evolution\ValueObjects\WorldSnapshot;
use WorldOS\Domains\Evolution\Enums\CivilizationPhase;

/**
 * FieldReactionEngine
 * 
 * "Thần thoại hóa" vật lý. Không còn sự kiện ngẫu nhiên thuần túy.
 * Sự kiện hiện tại là phản ứng của thực tại khi các thông số của "Trường" biến động quá nhanh.
 */
class FieldReactionEngine
{
    public function __construct(
        private EventEngine $legacyEventEngine,
        private DynamicsAnalyzer $dynamicsAnalyzer
    ) {}

    public function generateReactions(
        CivilizationSnapshot $currentCiv,
        CivilizationSnapshot $prevCiv,
        CivilizationPhase $phase,
        string $seed,
        array $pressures,
        float $totalFactionPower = 1.0
    ): array {
        // 1. Calculate Field Metrics
        $curvature = $this->dynamicsAnalyzer->calculateCurvature($currentCiv, $prevCiv);
        $divergence = $this->dynamicsAnalyzer->calculateDivergence($currentCiv);

        $reactions = [];

        // 2. High Curvature -> "Shock" Events (Thiên tai, Cách mạng, Biến cố lớn)
        // Raised threshold to 0.15 for 10-dimension stability
        if ($curvature > 0.15) {
            $intensity = min(1.0, ($curvature - 0.15) * 5.0 + 0.3);
            $reactions[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'field_shock',
                'name' => ($currentCiv->internalEntropy > 0.5) ? 'civil_unrest' : 'natural_disaster',
                'intensity' => round($intensity, 4),
                'scale' => rand(3, 5),
                'success' => false,
                'description' => "Thực tại bị uốn cong mạnh do biến động đột ngột ($curvature)"
            ];
        }

        // 3. High Divergence -> "Glitch" Events (Hiện tượng siêu nhiên, dị thường)
        if ($divergence > 0.15) {
            $reactions[] = [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'anomaly',
                'name' => 'metaphysical_fracture',
                'intensity' => round($divergence, 4),
                'scale' => rand(2, 4),
                'success' => false,
                'description' => "Sự phân kỳ các chỉ số thực tại đạt mức báo động"
            ];
        }

        // 4. Baseline Events (Still use legacy engine but filtered by field tension)
        $tension = ($currentCiv->internalEntropy + (1.0 - $currentCiv->stability)) / 2;
        if ($tension > 0.3) {
            $legacyEvents = $this->legacyEventEngine->generateEvents($currentCiv, $phase, $seed, $pressures, $totalFactionPower);
            // Filter: Only keep intense events if tension is low
            foreach ($legacyEvents as $event) {
                if (($event['intensity'] ?? 0) > (1.0 - $tension)) {
                    $reactions[] = $event;
                }
            }
        }

        return $reactions;
    }
}
