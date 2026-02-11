<?php

namespace App\Domains\Saga\Services;

use App\Domains\Genre\Signal\GenreProfile;

class NarrativeTagger
{
    private const GENRE_MAP = [
        'xianxia' => ['Tiên Hiệp', 'Huyền Ảo', 'Tu Chân'],
        'survival' => ['Sinh Tồn', 'Mạt Thế', 'Kịch Tính'],
        'urban_fantasy' => ['Đô Thị', 'Dị Năng', 'Huyền Bí'],
        'historical' => ['Dã Sử', 'Quân Sự', 'Cổ Đại'],
        'mundane' => ['Đời Thường', 'Tâm Lý'],
    ];

    private const COMPOSITE_TAGS = [
        'xianxia_survival' => ['Tiên Hiệp Mạt Thế', 'Khốc Liệt'],
        'urban_survival' => ['Đô Thị Sinh Tồn', 'Zombie'],
    ];

    public function generateTags(GenreProfile $profile): array
    {
        $tags = [];
        $weights = $profile->weights;
        
        // 1. Dominant Genre Tags
        $dominant = $profile->dominantGenre();
        if (isset(self::GENRE_MAP[$dominant])) {
            $tags = array_merge($tags, self::GENRE_MAP[$dominant]);
        }

        // 2. Secondary Genre Tags (if weight > 0.3)
        foreach ($weights as $genre => $weight) {
            if ($genre !== $dominant && $weight > 0.3) {
                if (isset(self::GENRE_MAP[$genre])) {
                    // Take only the first tag for secondary genres to avoid clutter
                    $tags[] = self::GENRE_MAP[$genre][0];
                }
                
                // Check for composite styles
                $compositeKey = "{$dominant}_{$genre}";
                if (isset(self::COMPOSITE_TAGS[$compositeKey])) {
                    $tags = array_merge($tags, self::COMPOSITE_TAGS[$compositeKey]);
                }
                
                // Inverse composite check
                $compositeKeyInverse = "{$genre}_{$dominant}";
                if (isset(self::COMPOSITE_TAGS[$compositeKeyInverse])) {
                    $tags = array_merge($tags, self::COMPOSITE_TAGS[$compositeKeyInverse]);
                }
            }
        }

        return array_values(array_unique($tags));
    }
}
