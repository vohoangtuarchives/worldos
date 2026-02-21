<?php

namespace Tuzy\Application\Genre\Genres\Xianxia;

use Tuzy\Domain\Genre\Contracts\VocabularyMap;

class XianxiaVocabulary implements VocabularyMap
{
    private array $terms = [
        'energy' => 'Spirit Qi',
        'school' => 'Sect',
        'leader' => 'Sect Patriarch',
        'student' => 'Disciple',
        'elite' => 'Elder',
        'weapon' => 'Dharma Treasure',
        'currency' => 'Spirit Stones',
        'text' => 'Jade Slip',
        'conflict' => 'Daoist Magic Battle',
        'gathering' => 'Dao Discussion',
        'tribulation' => 'Heavenly Tribulation',
        'breakthrough' => 'Realm Breakthrough',
        'meditation' => 'Closed Door Cultivation',
    ];

    public function term(string $key): string
    {
        return $this->terms[$key] ?? $key;
    }

    public function all(): array
    {
        return $this->terms;
    }
}
