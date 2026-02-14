<?php

namespace App\Domains\Social\Honorifics\Dictionaries;

use App\Domains\Social\Honorifics\HonorificDictionaryInterface;

class SurvivalHonorificDictionary implements HonorificDictionaryInterface
{
    public function selfReferences(): array
    {
        return [
            'kẻ này', 'người này', 'ta'
        ];
    }

    public function addressingOthers(): array
    {
        return [
            'người kia', 'các vị', 'ngươi'
        ];
    }

    public function socialTitles(): array
    {
        return [
            'thủ lĩnh', 'trưởng đoàn', 'kẻ dẫn đường'
        ];
    }
}
