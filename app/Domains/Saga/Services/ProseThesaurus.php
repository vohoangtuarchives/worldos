<?php

namespace App\Domains\Saga\Services;

/**
 * ProseThesaurus - A rich linguistic bank for narrative expansion.
 * 
 * Goal: Provide high-quality synonyms and evocative phrases to avoid repetition.
 */
class ProseThesaurus
{
    private array $data = [
        'conflict' => [
            'colloquial' => ['tranh chấp', 'đánh nhau', 'xung đột'],
            'literary' => ['can qua', 'biến động', 'giông bão'],
            'han_viet' => ['tranh hùng', 'huyết chiến', 'hạo kiếp', 'phong vân'],
            'extreme' => ['long trời lở đất', 'quỷ khóc thần sầu', 'máu chảy thành sông'],
        ],
        'hunger' => [
            'colloquial' => ['đói bụng', 'hết đồ ăn', 'cơn đói'],
            'literary' => ['cơn đói cồn cào', 'sự thiếu hụt', 'bụng đói dạ héo'],
            'han_viet' => ['tứ bề thọ địch', 'họa đói kém', 'sinh linh đồ thán'],
            'extreme' => ['đói thấu tận xương tủy', 'nghìn năm hiếm thấy', 'địa ngục trần gian'],
        ],
        'fear' => [
            'colloquial' => ['sợ hãi', 'lo lắng', 'run rẩy'],
            'literary' => ['nỗi kinh hoàng', 'sự bất an', 'tim đập chân run'],
            'han_viet' => ['kinh tâm động phách', 'vạn phần kinh hãi', 'hồn xiêu phách lạc'],
            'extreme' => ['tử khí bao trùm', 'nỗi sợ thấu tận tâm can', 'tuyệt vọng cực độ'],
        ],
        'change' => [
            'colloquial' => ['thay đổi', 'biến đổi', 'khác xưa'],
            'literary' => ['sự chuyển mình', 'bước ngoặt', 'biến chuyển'],
            'han_viet' => ['vận mệnh xoay vần', 'vật đổi sao dời', 'thiên địa biến hóa'],
            'extreme' => ['long trời lở đất', 'vận thế đảo điên', 'càn khôn nghịch chuyển'],
        ],
        'peace' => [
            'colloquial' => ['yên bình', 'an lành', 'không có việc gì'],
            'literary' => ['thanh bình', 'tĩnh lặng', 'nhịp sống êm ềm'],
            'han_viet' => ['thái bình thịnh thế', 'an lạc đại địa', 'phong hòa nhật lệ'],
            'extreme' => ['yên bình như mặt hồ lặng sóng', 'thiên đường hạ giới'],
        ]
    ];

    /**
     * Get a random synonym based on category and style.
     */
    public function getSynonym(string $category, string $style = 'literary', bool $extreme = false): string
    {
        $pool = $this->data[$category] ?? $this->data['change'];
        
        $options = $pool[$style] ?? $pool['literary'];
        
        if ($extreme && isset($pool['extreme'])) {
            $options = array_merge($options, $pool['extreme']);
        }

        return $options[array_rand($options)];
    }

    /**
     * Randomly replace keywords in a text with rich synonyms.
     */
    public function enrich(string $text, string $style = 'literary', int $density = 50): string
    {
        foreach ($this->data as $category => $styles) {
            if (rand(1, 100) > $density) continue;

            $synonym = $this->getSynonym($category, $style);
            $text = str_replace($category, $synonym, $text);
        }
        return $text;
    }
}
