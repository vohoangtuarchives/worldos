<?php

namespace WorldOS\Applications\Narrative;

use WorldOS\Domains\Evolution\WorldStateRepository;
use WorldOS\Domains\Narrative\Archive;
use WorldOS\Domains\Narrative\Episode;
use WorldOS\Infrastructure\AI\LanguageModelAdapter;
use Ramsey\Uuid\Uuid;

use WorldOS\Domains\Evolution\CivilizationState;

class GenerateEpisodeUseCase
{
    public function __construct(
        private WorldStateRepository $worldStateRepository,
        private LanguageModelAdapter $aiAdapter 
    ) {}

    public function execute(string $worldId, Archive $archive, ?CivilizationState $civ = null): Episode
    {
        // 1. Chỉ Read-Only World State
        $worldState = $this->worldStateRepository->findById($worldId);
        
        if (!$worldState) {
            throw new \InvalidArgumentException("World not found.");
        }

        // 2. Build Prompt dựa trên State + Epistemic Instability
        $systemPrompt = "Bạn là Nhà quan sát vĩ đại của Thế giới [{$worldId}]. Nhiệm vụ của bạn là dệt nên một thiên sử thi hoành tráng (Narrative Arc) dựa trên bối cảnh hiện tại.";
        
        $userPrompt = "=== Các Định Luật Bất Biến (Giả thuyết vũ trụ) ===\n";
        foreach ($worldState->getCoreTruth()->getAxioms() as $axiom) {
            $userPrompt .= "- {$axiom->description}\n";
        }

        $userPrompt .= "\n=== Tình Trạng Thế Giới ===\n";
        $userPrompt .= "- Global Entropy (Mức độ hỗn loạn): " . $worldState->getGlobalEntropy() . "\n";

        $instability = $archive->getEpistemicInstability();
        $userPrompt .= "\n=== Mức Căn Cơ Nhận Thức Lịch Sử ===\n";
        if ($instability > 0.7) {
            $userPrompt .= "CẢNH BÁO: Kỷ nguyên này ngập chìm trong Màn sương Nhận Thức (Epistemic Fog). Không ai biết rõ sự thật. Các ghi chép lịch sử mâu thuẫn lẫn nhau, thần thoại hóa các nhân vật có thật.\n";
        } elseif ($instability < 0.3) {
            $userPrompt .= "Kho lưu trữ lịch sử tương đối rõ ràng, các sự kiện quá khứ được truyền lại khá chính xác.\n";
        } else {
            $userPrompt .= "Nhận thức về quá khứ đang bị bào mòn. Sự thật và lời đồn đan xen.\n";
        }

        $userPrompt .= "\n=== Lưu Trữ Ký Ức Lịch Sử (Theo góc nhìn của Văn minh) ===\n";
        $recentEpisodes = $archive->getRecentEpisodes(5); // Lấy 5 Arc gần nhất
        if (empty($recentEpisodes)) {
            $userPrompt .= "[Chưa có sự kiện lịch sử nào được ghi nhận rõ ràng]\n";
        } else {
            foreach ($recentEpisodes as $ep) {
                // Giả lập mức độ nhiễu loạn
                $prefix = $instability > 0.5 ? "Tương truyền rằng:" : "Ghi chép lịch sử:";
                $userPrompt .= "- {$prefix} {$ep->getTitle()} - {$ep->getContent()}\n";
            }
        }
        
        if ($civ) {
            $userPrompt .= "\n=== Tình Trạng Văn Minh ===\n";
            $userPrompt .= "- Áp lực Bất bình đẳng: " . $civ->getInequality() . " / 1.0\n";
            $userPrompt .= "- Nỗi Phẫn Uất Xã Hội (Social Unrest): " . $civ->getResidual()->socialUnrest . " / 1.0\n";
            $userPrompt .= "- Sang chấn Chiến tranh (War Trauma): " . $civ->getResidual()->warTrauma . " / 1.0\n";
            $userPrompt .= "- Sẹo Siêu hình (Metaphysical Scar): " . $civ->getResidual()->metaphysicalScar . " / 1.0\n";
        }

        $userPrompt .= "\n=== Yêu cầu Sáng Tác ===\n";
        $userPrompt .= "Dựa vào Kho lưu trữ trên và những Vết sẹo của nền văn minh, hãy đề xuất 3 Hạt giống Cốt truyện (Plot Vectors) có khả năng bùng nổ thành một Arc lớn trong kỷ nguyên này.\n";
        
        // 3. Call AI
        $content = $this->aiAdapter->generate($systemPrompt, $userPrompt);
        
        // 4. Record to Archive
        $episode = new Episode(
            Uuid::uuid4()->toString(),
            "Kỷ Nguyên Biến Động", 
            $content,
            ['ai_generated'],
            new \DateTimeImmutable()
        );
        
        $archive->recordEpisode($episode);
        
        return $episode;
    }
}
