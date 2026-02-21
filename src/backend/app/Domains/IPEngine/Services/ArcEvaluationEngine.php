<?php

namespace App\Domains\IPEngine\Services;

class ArcEvaluationEngine
{
    /**
     * AI Critic: evaluates the proposed text against the laws of the universe.
     * Ensure the story doesn't violate Core Truths while allowing Epistemic freedom.
     */
    public function createEvaluationPrompt(string $proposedPlot, array $axioms): string
    {
        $prompt = "Bạn là Đấng Phán Xét Kỷ Nguyên (Critic Agent). Hãy xem xét Hạt giống Cốt truyện sau:\n";
        $prompt .= "=== CỐT TRUYỆN ĐƯỢC ĐỀ XUẤT ===\n";
        $prompt .= $proposedPlot . "\n\n";

        $prompt .= "=== QUY LUẬT BẤT BIẾN CỦA VŨ TRỤ (CORE AXIOMS) ===\n";
        foreach ($axioms as $axiom) {
            $prompt .= "- {$axiom->id}: {$axiom->description}\n";
        }
        $prompt .= "\n";

        $prompt .= "Nhiệm vụ của bạn:\n";
        $prompt .= "1. (HARD RULE): Các nhân vật và phép màu TRONG TRUYỆN không được phép VI PHẠM Core Axioms. Nếu có, hãy REJECT.\n";
        $prompt .= "2. (SOFT RULE): Các nhân vật CÓ THỂ hiểu sai về Core Axioms (Epistemic Error), nhưng bản thân quy luật thực tế không thay đổi.\n";
        $prompt .= "3. Đánh giá tính hấp dẫn, độ dồn nén cảm xúc (Tension) và mức độ khai thác triệt để 'Nỗi Đau Lịch Sử' (Residuals).\n";

        $prompt .= "Trả về kết quả chuẩn JSON:\n";
        $prompt .= "{\n";
        $prompt .= "  \"status\": \"APPROVED|REJECTED\",\n";
        $prompt .= "  \"reason\": \"...\",\n";
        $prompt .= "  \"suggested_fixes\": []\n";
        $prompt .= "}\n";

        return $prompt;
    }
}
