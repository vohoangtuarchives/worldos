<?php

namespace App\Domains\Genre\Genres\Xianxia;

use App\Domains\Genre\Contracts\MaterialSystem;

class QiMaterialSystem implements MaterialSystem
{
    public function primary(): string 
    { 
        return 'Qi'; 
    }

    public function subtypes(): array
    {
        return ['Heavenly Qi', 'Earth Qi', 'Demonic Qi', 'Sword Qi'];
    }

    public function conversionRules(): array
    {
        return [
            'spirit_stone' => 'Qi',
            'blood_essence' => 'Demonic Qi',
            'monster_core' => 'Qi',
            'alchemy_pill' => 'Qi',
        ];
    }
}
