<?php

namespace WorldOS\Legacy\Application\IPEngine\Services;

use WorldOS\Legacy\Application\WorldEvolution\Entities\WorldState;
use WorldOS\Legacy\Application\Epistemology\Entities\PerceivedArchive;
use WorldOS\Legacy\Application\CivilizationDynamics\Entities\CivilizationState;

class AIPromptBuilder
{
    /**
     * Build the context prompt for the AI Planner or AI Writer.
     * The AI only gets the Perceived Archive (Myth & Distorted truth) and the 
     * current pressures of the civilization to generate a narrative arc.
     */
    public function buildNarrativeContextPrompt(
        WorldState $world,
        CivilizationState $civ,
        PerceivedArchive $perceptions
    ): string {
        $prompt = "Bạn là Nhà quan sát vĩ đại của Thế giới [{$civ->sagaId} - {$civ->universeId}].\n";
        $prompt .= "Nhiệm vụ của bạn là dệt nên một thiên sử thi hoành tráng (Narrative Arc) dựa trên bối cảnh hiện tại.\n\n";

        // 1. Immutable Truth (But obscured)
        $prompt .= "=== Các Định Luật Bất Biến (Giả thuyết vũ trụ) ===\n";
        foreach ($world->coreTruth->axioms as $axiom) {
            $prompt .= "- {$axiom->description}\n";
        }
        $prompt .= "\n";

        // 2. Epistemic Instability (Fog of War for History)
        $clarity = $world->epistemicIndex->clarity;
        $instability = $world->epistemicIndex->instability;

        $prompt .= "=== Mức Căn Cơ Nhận Thức Lịch Sử ===\n";
        if ($instability > 0.7) {
            $prompt .= "CẢNH BÁO: Kỷ nguyên này ngập chìm trong Màn sương Nhận Thức (Epistemic Fog). Không ai biết rõ sự thật. Các ghi chép lịch sử mâu thuẫn lẫn nhau, thần thoại hóa các nhân vật có thật.\n";
        } elseif ($clarity > 0.7) {
            $prompt .= "Kho lưu trữ lịch sử tương đối rõ ràng, các sự kiện quá khứ được truyền lại khá chính xác.\n";
        } else {
            $prompt .= "Nhận thức về quá khứ đang bị bào mòn. Sự thật và lời đồn đan xen.\n";
        }
        $prompt .= "\n";

        // 3. The Perceived Archive
        $prompt .= "=== Lưu Trữ Ký Ức Lịch Sử (Theo góc nhìn của Văn minh) ===\n";
        $facts = $perceptions->fetchForPlanner();
        if (empty($facts)) {
            $prompt .= "[Chưa có sự kiện lịch sử nào được ghi nhận rõ ràng]\n";
        } else {
            foreach ($facts as $fact) {
                // If instability is high, we prompt the AI to treat this fact as a "myth" or "legend" rather than hard truth
                $prefix = $instability > 0.5 ? "Tương truyền rằng:" : "Ghi chép lịch sử:";
                $prompt .= "- Năm {$fact->year}: {$prefix} {$fact->description} (Mức chấn động: {$fact->impactScore})\n";
            }
        }
        $prompt .= "\n";

        // 4. Civilization Pressures & Scars
        $prompt .= "=== Tình Trạng Văn Minh [{$civ->name}] (Năm {$world->currentYear}) ===\n";
        foreach ($civ->residualMemory->getScars() as $type => $magnitude) {
            $prompt .= "- Nỗi Phẫn Uất/Sẹo Lịch Sử ({$type}): {$magnitude} / 1.0\n";
        }

        $prompt .= "\n=== Yêu cầu Sáng Tác ===\n";
        $prompt .= "Dựa vào Kho lưu trữ trên và những Vết sẹo của nền văn minh, hãy đề xuất 3 Hạt giống Cốt truyện (Plot Vectors) có khả năng bùng nổ thành một Arc lớn trong kỷ nguyên này.\n";

        return $prompt;
    }
}
