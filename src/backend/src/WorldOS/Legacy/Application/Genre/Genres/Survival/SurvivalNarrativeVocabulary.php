<?php

namespace WorldOS\Legacy\Application\Genre\Genres\Survival;

use WorldOS\Legacy\Domain\Genre\Contracts\VocabularyMap;

class SurvivalNarrativeVocabulary implements VocabularyMap
{
    public function term(string $key): string
    {
        return match ($key) {
            'death' => 'sinh cơ đoạn tuyệt',
            'hunger' => 'phúc vị cạn kiệt',
            'thirst' => 'yết hầu khô kiệt',
            'fatigue' => 'thể lực suy tàn',
            'night' => 'dạ sắc trầm mặc',
            'wound' => 'huyết thương rách nát',
            'fear' => 'tâm thần dao động',
            'silence' => 'tĩnh mịch tuyệt đối',
            'hope' => 'kỳ tích hão huyền',
            'food' => 'nguồn sống',
            'water' => 'thủy nguyên',
            'enemy' => 'mối đe dọa',
            default => $key,
        };
    }
}
