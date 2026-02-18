<?php

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\Entities\Universe;

class SagaGeneratorService
{
    private \App\Domains\Narrative\Services\NarrativeBridge $narrativeBridge;

    /** Nhiều biến thể kết thúc theo cause — từ ngữ và văn phong đa dạng */
    private const ENDINGS = [
        'HEAT_DEATH' => [
            'Cuối cùng, định luật nhiệt động học không thể chối bỏ. Entropy nuốt chửng mọi cấu trúc, chỉ còn lại khoảng không lạnh lẽo và im lặng (Nhiệt chết).',
            'Trật tự nhường chỗ cho hỗn mang. Thế giới tắt dần trong cái lạnh vô tận của entropy tối đa (Heat Death).',
        ],
        'TIME_CRUNCH' => [
            'Bản thân thời gian rã ra. Vũ trụ sụp đổ dưới sức nặng lịch sử của chính nó, kết thúc trong một kỳ dị thuần dữ liệu (Time Crunch).',
        ],
        'STAGNATION' => [
            'Sự hoàn hảo trở thành nhà tù. Không còn thay đổi, sự sống mất hết ý nghĩa; vũ trụ hóa thành một tượng đài tĩnh tại, vĩnh cửu (Trì trệ).',
            'Trật tự tuyệt đối và entropy gần như bằng không khiến thế giới đóng băng — không còn khả năng thích nghi hay tiến hóa (Stagnation).',
        ],
        'STRUCTURAL_FRACTURE' => [
            'Đạo của thế giới rạn nứt. Mâu thuẫn tích tụ vượt ngưỡng chịu đựng; cấu trúc sụp đổ từ bên trong (Structural Fracture).',
            'Sự rạn nứt cấu trúc không thể hàn gắn. Thế giới tan vỡ dưới áp lực của chính những mâu thuẫn nội tại.',
        ],
        'CONVERGENCE' => [
            'Dòng thời gian riêng của nó đã hòa vào một điểm đồng bộ tuyệt đối với một thực tại khác. Hai thành một, vượt lên (Convergence).',
        ],
        'BIFURCATION' => [
            'Độ phức tạp của những lựa chọn vượt quá sức chứa của một dòng thời gian. Nó đã tách thành những nhánh tồn tại song song (Bifurcation).',
        ],
        'default' => [
            'Nó kết thúc đột ngột, số phận đóng ấn bởi những lực lượng vũ trụ không rõ.',
            'Thế giới chấm dứt trong một cách mà biên niên sử không ghi chép đầy đủ.',
        ],
    ];

    public function __construct(\App\Domains\Narrative\Services\NarrativeBridge $narrativeBridge)
    {
        $this->narrativeBridge = $narrativeBridge;
    }

    public function generateSaga(Universe $universe, string $deathCause): string
    {
        $state = $universe->getState();
        $age = $universe->getAge();
        $id = substr($universe->getId(), 0, 8);
        $name = "Universe " . $id;

        $genre = $this->narrativeBridge->detectGenre($state);
        $primaryGenre = $genre->getPrimaryGenre();
        $traits = implode(', ', $genre->getTraits());

        $params = $universe->getParameters();
        $ancestors = $params['ancestors'] ?? [];
        if (!empty($ancestors)) {
            $shortAncestors = array_map(fn ($aid) => substr($aid, 0, 8), $ancestors);
            $originPrefix = "Sinh ra từ dòng dõi [" . implode(', ', $shortAncestors) . "], nó ";
        } else {
            $originPrefix = "Nổi lên từ bọt lượng tử như ";
        }

        $intro = "{$originPrefix} một thực tại **{$primaryGenre}** với các đặc trưng [{$traits}].";
        $mid = $this->getMiddle($universe);
        $end = $this->getEnding($deathCause);

        return "{$name}. {$intro} {$mid} {$end}";
    }

    private function getMiddle(Universe $u): string
    {
        $context = $this->narrativeBridge->generateContext($u->getState());
        $age = $u->getAge();
        $params = $u->getParameters();
        $milestoneStr = "";
        if (!empty($params['milestones'])) {
            $milestoneStr = "\n\n**Dòng thời gian:**\n";
            foreach (array_slice($params['milestones'], -8) as $m) {
                $milestoneStr .= "- Chu kỳ {$m['age']}: **{$m['event']}** — {$m['description']}\n";
            }
        }
        return "Trong {$age} chu kỳ tồn tại, mô phỏng ghi nhận: \"{$context}\"{$milestoneStr}";
    }

    private function getEnding(string $cause): string
    {
        $pool = self::ENDINGS[$cause] ?? self::ENDINGS['default'];
        return $pool[array_rand($pool)];
    }
}
