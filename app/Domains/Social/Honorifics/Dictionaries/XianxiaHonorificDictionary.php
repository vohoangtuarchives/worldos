<?php

namespace App\Domains\Social\Honorifics\Dictionaries;

use App\Domains\Social\Honorifics\HonorificDictionaryInterface;

class XianxiaHonorificDictionary implements HonorificDictionaryInterface
{
    public function selfReferences(): array
    {
        return [
            'bản tọa', 'tại hạ', 'bần đạo', 'tiểu bối', 'lão phu', 'lão quái', 'cổ giả'
        ];
    }

    public function addressingOthers(): array
    {
        return [
            'đạo hữu', 'các hạ', 'tiền bối', 'hậu bối', 'chư vị', 'tôn giả', 'sư huynh', 'sư tỷ', 'sư muội', 'sư đệ'
        ];
    }

    public function socialTitles(): array
    {
        return [
            'chưởng môn', 'trưởng lão', 'hộ pháp', 'đạo chủ', 'thánh tử', 'tông chủ', 'thái thượng trưởng lão'
        ];
    }
}
