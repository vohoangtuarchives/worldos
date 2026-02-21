<?php

namespace Tuzy\Domain\Power;

use Tuzy\Domain\Power\ValueObject\PowerStage;

class PowerStageRegistry
{
    public function getStageAndConstraint(string $stageKey): array
    {
        return match ($stageKey) {
            'mundane' => [
                'stage' => PowerStage::STAGE_0_MUNDANE, // Stage 0
                'constraint' => 'Vật lý thuần túy. Không có phép thuật công khai.',
                'ceiling' => 'Người thường (Limit Break rất hiếm)',
                'vocabulary_profile' => 'mundane_urban', // Hiện đại, khoa học
                'allowed_events' => ['crime', 'politics', 'discovery'],
                'forbidden_terms' => ['linh khí', 'tiên đế', 'ma pháp'],
                'public_awareness' => 'unaware',
                'transition_trigger' => 'spirit_leak_0_1'
            ],
            'mortal_martial' => [
                'stage' => PowerStage::STAGE_1_MORTAL_MARTIAL, // Stage 1
                'constraint' => 'Võ học thể phách. Nội kình sơ khai. Bị che giấu.',
                'ceiling' => 'Võ Sư (Body Refinement)',
                'vocabulary_profile' => 'hidden_world', // Giang hồ, ám chỉ
                'allowed_events' => ['sect_conflict', 'underground_tournament'],
                'forbidden_terms' => ['nguyên anh', 'phi thăng'],
                'public_awareness' => 'rumor', // Tin đồn đô thị
                'transition_trigger' => 'seal_crack_0_4'
            ],
            'enhanced_martial' => [
                'stage' => PowerStage::STAGE_2_ENHANCED_MARTIAL, // Stage 2
                'constraint' => 'Chân khí ngoại phóng. Dị năng thức tỉnh.',
                'ceiling' => 'Tông Sư (Qi Condensation)',
                'vocabulary_profile' => 'enhanced_urban', // Bắt đầu dùng từ Hán Việt nhẹ
                'allowed_events' => ['small_breach', 'supernatural_crime'],
                'forbidden_terms' => ['đại đạo', 'quy tắc'],
                'public_awareness' => 'leak', // Clip trên mạng, chính phủ che đậy
                'transition_trigger' => 'rift_stable_0_7'
            ],
            'low_immortal' => [
                'stage' => PowerStage::STAGE_3_LOW_IMMORTAL, // Stage 3
                'constraint' => 'Linh khí khôi phục. Pháp thuật can thiệp vật lý.',
                'ceiling' => 'Trúc Cơ / Kết Đan (Foundation)',
                'vocabulary_profile' => 'xianxia_low', // Hán Việt chiếm ưu thế
                'allowed_events' => ['spirit_tide', 'beast_invasion'],
                'forbidden_terms' => ['thần cách'],
                'public_awareness' => 'public', // Công khai, trật tự xã hội đảo lộn
                'transition_trigger' => 'dimensional_merge_1_0'
            ],
            'high_immortal' => [
                'stage' => PowerStage::STAGE_4_HIGH_IMMORTAL, // Stage 4
                'constraint' => 'Quy tắc thế giới bị viết lại. Thành phố bay.',
                'ceiling' => 'Nguyên Anh / Hóa Thần',
                'vocabulary_profile' => 'xianxia_high',
                'allowed_events' => ['sect_war_global', 'law_rewrite'],
                'forbidden_terms' => [],
                'public_awareness' => 'adapted', // Xã hội thích nghi với luật mới
                'transition_trigger' => 'mythic_descent'
            ],
            'mythic' => [
                'stage' => PowerStage::STAGE_5_MYTHIC, // Stage 5
                'constraint' => 'Thần thoại tái lâm. Không gian đa chiều.',
                'ceiling' => 'Chân Tiên / Thần',
                'vocabulary_profile' => 'mythic',
                'allowed_events' => ['world_collapse', 'timeline_war'],
                'forbidden_terms' => [],
                'public_awareness' => 'worship', // Thờ phụng hoặc diệt vong
                'transition_trigger' => 'none'
            ],
            default => []
        };
    }
}
