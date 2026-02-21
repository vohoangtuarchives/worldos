<?php

namespace Tuzy\Application\Power\Rules;

use Tuzy\Domain\Power\ValueObject\PowerStage;

class StageTransitionNarrativeRule
{
    public function getRulesForTransition(PowerStage $from, PowerStage $to): array
    {
        $key = "{$from->value}_{$to->value}";

        return match ($key) {
            'mundane_mortal_martial' => [
                'emotion' => 'disbelief', // Ngỡ ngàng
                'focus' => 'hidden_discovery',
                'forbidden_tropes' => ['world_saving', 'public_hero'],
                'required_elements' => ['secrecy', 'urban_camouflage'],
                'narrative_guideline' => 'Nhân vật chính cảm thấy thế giới quen thuộc bỗng trở nên xa lạ. Võ học xuất hiện nhưng bị che giấu kỹ lưỡng.'
            ],
            'mortal_martial_enhanced_martial' => [
                'emotion' => 'unease', // Bất an
                'focus' => 'containment_breach',
                'forbidden_tropes' => ['government_collapse'],
                'required_elements' => ['rumors', 'leaked_footage'],
                'narrative_guideline' => 'Tin đồn lan rộng. Các video clip về sức mạnh siêu nhiên xuất hiện trên mạng nhưng bị xóa nhanh chóng.'
            ],
            'enhanced_martial_low_immortal' => [
                'emotion' => 'fear', // Sợ hãi
                'focus' => 'reality_shift',
                'forbidden_tropes' => ['peaceful_resolution'],
                'required_elements' => ['mass_panic', 'physical_law_break'],
                'narrative_guideline' => 'Linh khí khôi phục. Các định luật vật lý bắt đầu bị bẻ cong công khai. Xã hội hoảng loạn.'
            ],
            default => [
                'emotion' => 'neutral',
                'focus' => 'progression',
            ]
        };
    }

    public function getVocabularyShift(PowerStage $to): array
    {
        return match ($to) {
            PowerStage::STAGE_1_MORTAL_MARTIAL => [
                'deprecated' => ['thể thao', 'vô địch olympic'],
                'emerging' => ['nội kình', 'cổ võ', 'ẩn thế'],
            ],
            PowerStage::STAGE_3_LOW_IMMORTAL => [
                'deprecated' => ['cao thủ', 'nội công'],
                'emerging' => ['linh khí', 'trúc cơ', 'pháp thuật'],
            ],
            default => []
        };
    }
}
