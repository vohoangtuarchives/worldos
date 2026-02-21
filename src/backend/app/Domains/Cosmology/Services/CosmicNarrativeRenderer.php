<?php

declare(strict_types=1);

namespace App\Domains\Cosmology\Services;

use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use Tuzy\Domain\Cosmology\ValueObject\CivilizationState;

/**
 * CosmologyNarrativeRenderer
 *
 * "Narrative as Renderer" — interprets the simulation state into text.
 * Reads ONLY. Never modifies state. Zero coupling to simulation logic.
 *
 * This renderer is separate from the physics engine by design:
 * the simulation produces numbers, the renderer produces prose.
 */
class CosmicNarrativeRenderer
{
    /**
     * Render a narrative description of the current cosmic state.
     *
     * @param WorldSnapshot $snapshot Current world state
     * @param array $events Events from the last step (if any)
     * @return array{cosmic: string, environment: string, civilization: string, tension: string, events: array<string>}
     */
    public function render(WorldSnapshot $snapshot, array $events = []): array
    {
        return [
            'cosmic' => $this->renderCosmic($snapshot),
            'environment' => $this->renderEnvironment($snapshot),
            'civilization' => $this->renderCivilization($snapshot),
            'tension' => $this->renderTension($snapshot),
            'events' => $this->renderEvents($events),
        ];
    }

    private function renderCosmic(WorldSnapshot $snapshot): string
    {
        $cosmic = $snapshot->cosmic;
        $energy = $cosmic->energy;
        $entropy = $cosmic->entropy;
        $attractor = $cosmic->currentAttractor;

        $energyDesc = match (true) {
            $energy > 0.8 => 'Thiên khí trào dâng mãnh liệt, năng lượng vũ trụ ở đỉnh cao',
            $energy > 0.6 => 'Linh khí ổn định, dòng chảy vũ trụ hài hòa',
            $energy > 0.4 => 'Thiên khí suy yếu, năng lượng vũ trụ đang thoái trào',
            default       => 'Thiên khí kiệt quệ, hư không bao trùm vạn vật',
        };

        $entropyDesc = match (true) {
            $entropy > 0.7 => 'Hỗn mang lan tràn, trật tự vũ trụ đang tan rã',
            $entropy > 0.4 => 'Nhiễu loạn gia tăng, ranh giới giữa trật tự và hỗn mang mờ nhạt',
            $entropy > 0.2 => 'Vũ trụ vận hành theo quy luật, trật tự được duy trì',
            default        => 'Trật tự tuyệt đối, vũ trụ tĩnh lặng như mặt nước hồ thu',
        };

        $attractorName = match ($attractor) {
            'EQUILIBRIUM'        => 'Thiên Hòa',
            'HIGH_CHAOS'         => 'Thiên Loạn',
            'RESONANCE_DOMINANT' => 'Thiên Minh',
            'VOID_COLLAPSE'      => 'Thiên Diệt',
            default              => str_contains($attractor, 'EMERGENT') ? 'Thiên Biến (Chế độ mới)' : $attractor,
        };

        return "{$energyDesc}. {$entropyDesc}. Vũ trụ đang trong chế độ [{$attractorName}].";
    }

    private function renderEnvironment(WorldSnapshot $snapshot): string
    {
        $env = $snapshot->environment;

        $terrain = match (true) {
            $env->terrainStability > 0.8 => 'Địa mạch vững chắc, đất đai phì nhiêu',
            $env->terrainStability > 0.5 => 'Địa mạch dao động, thỉnh thoảng có chấn động nhẹ',
            $env->terrainStability > 0.3 => 'Địa mạch bất ổn, động đất và sụp lở thường xuyên',
            default                      => 'Địa mạch gãy vỡ, đại địa chấn có thể xảy ra bất cứ lúc nào',
        };

        $anomaly = match (true) {
            $env->anomalyDensity > 0.5 => 'Dị tượng xuất hiện khắp nơi — vết rạn thực tại, xoáy không-thời gian',
            $env->anomalyDensity > 0.2 => 'Một số dị tượng đã được ghi nhận: ánh sáng bất thường, vùng năng lượng dị biến',
            default                    => 'Không gian yên bình, ít dị tượng',
        };

        return "{$terrain}. {$anomaly}.";
    }

    private function renderCivilization(WorldSnapshot $snapshot): string
    {
        $civ = $snapshot->civilization;

        $knowledge = match (true) {
            $civ->collectiveKnowledge > 1.5 => 'Văn minh đạt đỉnh cao tri thức, hiểu biết sâu sắc về quy luật vũ trụ',
            $civ->collectiveKnowledge > 0.8 => 'Tri thức phát triển, các phái tu luyện và học giả nghiên cứu thiên đạo',
            $civ->collectiveKnowledge > 0.3 => 'Tri thức còn sơ khai, dân chúng bắt đầu tìm hiểu thế giới',
            default                         => 'Dân chúng mông muội, sống theo bản năng',
        };

        $ritual = match (true) {
            $civ->ritualCoherence > 0.7 => 'Nghi lễ đồng bộ cao — toàn dân cộng hưởng với thiên đạo',
            $civ->ritualCoherence > 0.4 => 'Nghi lễ có tổ chức, nhưng chưa đạt mức cộng hưởng',
            default                     => 'Nghi lễ rời rạc, mỗi nơi mỗi kiểu',
        };

        $stability = match (true) {
            $civ->factionStability > 0.7 => 'Các thế lực hòa bình, xã hội ổn định',
            $civ->factionStability > 0.4 => 'Căng thẳng giữa các phe phái, nhưng chưa bùng nổ',
            default                      => 'Xung đột lan tràn, xã hội bên bờ vực sụp đổ',
        };

        return "{$knowledge}. {$ritual}. {$stability}.";
    }

    private function renderTension(WorldSnapshot $snapshot): string
    {
        $tension = $snapshot->compositeTension();

        return match (true) {
            $tension > 0.7 => '⚠️ CẢNH BÁO: Áp lực vũ trụ cực kỳ cao — Thiên Kiếp có thể xảy ra',
            $tension > 0.5 => '🔶 Áp lực gia tăng — các lực lượng vũ trụ đang hội tụ',
            $tension > 0.3 => '🔵 Áp lực vừa phải — vũ trụ vận hành bình thường',
            default        => '🟢 Bình yên — vạn vật hài hòa',
        };
    }

    private function renderEvents(array $events): array
    {
        $rendered = [];

        foreach ($events as $event) {
            $type = $event['type'] ?? 'UNKNOWN';
            $year = $event['year'] ?? '?';
            $from = $event['from'] ?? '?';
            $to = $event['to'] ?? '?';

            $rendered[] = match ($type) {
                'MINOR_BIFURCATION' => "🌀 Năm {$year}: Thiên Đạo chuyển biến — Chế độ [{$from}] → [{$to}]. Vạn vật cảm ứng, thời đại mới bắt đầu.",
                'MAJOR_BIFURCATION' => "💥 Năm {$year}: ĐẠI THIÊN BIẾN! Một chế độ vũ trụ chưa từng tồn tại đã xuất hiện: [{$to}]. Đây là khoảnh khắc mà lịch sử chia thành TRƯỚC và SAU.",
                default => "📌 Năm {$year}: Sự kiện vũ trụ [{$type}].",
            };
        }

        return $rendered;
    }
}
