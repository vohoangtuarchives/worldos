<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Serial;

use App\Domains\Saga\Services\GenesisPresetService;

/**
 * Preset cấu trúc và tone cho truyện dài kỳ theo thể loại.
 * Harry Potter style (fantasy_school), Tiếu Ngạo Giang Hồ style (wuxia), và 25 Genesis presets (25 loại truyện).
 */
final class SerialGenrePreset
{
    public const FANTASY_SCHOOL = 'fantasy_school';
    public const WUXIA = 'wuxia';
    /** Thể loại tự phát sinh từ world/universe (arc + tone từ evolution và world state). */
    public const EMERGENT = 'emergent';

    /** Số tập mặc định khi genre không có trong GENRE_BOOKS_COUNT hoặc preset không có narrative_books_count. Dùng 5 để thống nhất với fallback generic. */
    private const DEFAULT_BOOKS_COUNT = 5;

    /** Map Genesis genre → default books_count khi preset không có narrative_books_count. */
    private const GENRE_BOOKS_COUNT = [
        'xianxia' => 7,
        'wuxia' => 10,
        'court_intrigue' => 6,
        'military' => 6,
        'cyberpunk' => 5,
        'slice_of_life' => 5,
        'space_opera' => 7,
        'demon_realm' => 7,
        'dark_fantasy' => 6,
        'modern_fantasy' => 7,
        'ling_di' => 5,
        'occult' => 5,
        'entertainment' => 5,
        'medical' => 5,
        'dynasty' => 6,
        'pirate' => 6,
        'vr_world' => 7,
        'post_apocalyptic' => 6,
        'anime_power' => 7,
        'system' => 7,
        'cultivation_tech' => 7,
        'urban_fantasy' => 6,
        'multiverse_saga' => 7,
        'time_loop' => 6,
    ];

    /** Map Genesis genre → arc_templates khi preset không có narrative_arc_templates. */
    private const GENRE_ARC_TEMPLATES = [
        'xianxia' => [
            'Khởi đầu phàm nhân, nhập môn tu luyện',
            'Kỳ ngộ và đột phá cảnh giới',
            'Ân oán tông môn, tranh đấu tài nguyên',
            'Đại chiến hoặc đại hội',
            'Phi thăng hoặc mở ra thế giới mới',
        ],
        'wuxia' => [
            'Vào đời giang hồ, gặp kỳ duyên',
            'Học võ hoặc lĩnh ngộ bí kỹ',
            'Kết oán với môn phái hoặc cao thủ',
            'Đại hội võ lâm hoặc tranh bá',
            'Kết cục: thành danh hoặc quy ẩn',
        ],
        'court_intrigue' => [
            'Vào cung, làm quen quy tắc',
            'Liên minh và kình địch',
            'Biến cố và thanh trừng',
            'Thăng tiến hoặc sa cơ',
            'Đỉnh cao quyền lực hoặc thoái ẩn',
        ],
        'military' => [
            'Nhập ngũ hoặc chiến trường',
            'Thử thách và đồng đội',
            'Trận đánh then chốt',
            'Tổn thất và quyết định',
            'Hòa bình hoặc chiến thắng',
        ],
        'cyberpunk' => [
            'Sống trong bóng tối đô thị',
            'Vụ án hoặc nhiệm vụ nguy hiểm',
            'Megacorp và hack',
            'Đổi máu lấy sự thật',
            'Kết: nổi loạn hoặc tan biến',
        ],
        'slice_of_life' => [
            'Đời thường và quan hệ',
            'Xung đột nhỏ, thay đổi nội tâm',
            'Khoảnh khắc quyết định',
            'Hòa giải hoặc chấp nhận',
            'Kết mở hoặc bình yên',
        ],
        'space_opera' => [
            'Không gian, chiến tranh vì sao',
            'Liên minh và kẻ thù',
            'Trận chiến then chốt',
            'Hy sinh và chiến thắng',
            'Tân kỷ nguyên',
        ],
        'demon_realm' => [
            'Vào ma đạo, học cấm thuật',
            'Tranh đoạt tài nguyên và quyền lực',
            'Kình địch chính-phái',
            'Đại chiến hoặc thỏa hiệp',
            'Kết: trắc ẩn hoặc hủy diệt',
        ],
        'dark_fantasy' => [
            'Thế giới tối tăm, hiến tế và giá phải trả',
            'Cấm thuật và nguyền rủa',
            'Xung đột với thế lực hắc ám',
            'Cao trào và hy sinh',
            'Kết: tái sinh hoặc diệt vong',
        ],
        'modern_fantasy' => [
            'Phát hiện phép thuật ẩn trong thế giới hiện đại',
            'Học viện hoặc tổ chức bí mật',
            'Kẻ thù trỗi dậy',
            'Liên minh và chiến đấu',
            'Kết: bảo vệ thế giới hoặc cân bằng',
        ],
        'ling_di' => [
            'Trừ tà, phong thủy, bùa chú',
            'Bí ẩn và nghi lễ',
            'Đối đầu với linh dị',
            'Giải oan hoặc trấn yểm',
            'Kết: bình yên hoặc mở ra cánh cửa mới',
        ],
        'occult' => [
            'Nghi thức, triệu hồi, khế ước',
            'Giá phải trả của quyền lực',
            'Thực thể và ranh giới',
            'Cao trào nghi lễ',
            'Kết: đóng ấn hoặc vĩnh viễn hóa',
        ],
        'entertainment' => [
            'Bước chân vào showbiz',
            'Cạnh tranh và đồng đội',
            'Scandal hoặc bước ngoặt',
            'Đỉnh cao hoặc sa cơ',
            'Kết: thành danh hoặc lui về',
        ],
        'medical' => [
            'Thần y tái thế, cứu người',
            'Nghiên cứu và đột phá',
            'Xung đột với hệ thống cũ',
            'Đại dịch hoặc đại án',
            'Kết: thay đổi ngành y hoặc ẩn danh',
        ],
        'dynasty' => [
            'Vương triều, chính trị, chiến tranh',
            'Xây dựng quốc gia',
            'Nội chiến hoặc ngoại xâm',
            'Thăng trầm quyền lực',
            'Kết: thịnh trị hoặc đổi triều',
        ],
        'pirate' => [
            'Ra khơi, cướp biển, thủy thủ đoàn',
            'Khám phá vùng đất mới',
            'Tranh đoạt báu vật hoặc danh tiếng',
            'Đại chiến trên biển',
            'Kết: huyền thoại hoặc tan đàn',
        ],
        'vr_world' => [
            'Full-dive, NPC sentient',
            'Game trở thành thực tại',
            'Ranh giới thật – ảo mờ đi',
            'Quyền lực trong và ngoài game',
            'Kết: thức tỉnh hoặc hòa làm một',
        ],
        'post_apocalyptic' => [
            'Hậu tận thế, tiến hóa đột biến',
            'Kẻ mạnh sinh tồn',
            'Liên minh và kình địch',
            'Đại chiến hoặc thống nhất',
            'Kết: tân thế giới hoặc diệt vong',
        ],
        'anime_power' => [
            'Thức tỉnh năng lực, thế giới đặc thù',
            'Luyện tập và đối đầu',
            'Đại chiến hoặc giải đấu',
            'Hy sinh và trưởng thành',
            'Kết: bảo vệ hoặc đổi thay',
        ],
        'system' => [
            'Hệ thống thức tỉnh, bảng trạng thái',
            'Nhiệm vụ và leveling',
            'Thế giới mở ra theo cấp độ',
            'Boss và bước ngoặt',
            'Kết: đỉnh cao hoặc vượt hệ thống',
        ],
        'cultivation_tech' => [
            'Tu luyện gặp khoa học',
            'Đột phá công nghệ – tu vi',
            'Tranh đoạt tài nguyên vũ trụ',
            'Đại chiến hoặc hội nhập',
            'Kết: phi thăng hoặc tân kỷ nguyên',
        ],
        'urban_fantasy' => [
            'Đô thị hiện đại, dị năng thức tỉnh',
            'Tổ chức bí mật và quy tắc',
            'Xung đột siêu năng lực',
            'Âm mưu lớn và đối đầu',
            'Kết: cân bằng hoặc bùng nổ',
        ],
        'multiverse_saga' => [
            'Xuyên không gian, đa vũ trụ',
            'Luật lệ giữa các thế giới',
            'Tranh đoạt hoặc liên minh đa chiều',
            'Đại chiến xuyên vũ trụ',
            'Kết: thống nhất hoặc đa nguyên',
        ],
        'time_loop' => [
            'Lặp lại thời gian',
            'Thay đổi vận mệnh từng vòng',
            'Giá phải trả của nghịch thiên',
            'Bước ngoặt thoát vòng',
            'Kết: cải số hoặc chấp nhận',
        ],
        'default' => [
            'Mở đầu và giới thiệu thế giới',
            'Xung đột leo thang',
            'Bước ngoặt',
            'Cao trào',
            'Kết và hậu quả',
        ],
    ];

    /**
     * Số "tập" (book/volume) gợi ý và mô tả cấu trúc.
     * Hỗ trợ 3 legacy keys + 25 Genesis preset keys.
     *
     * @return array{books_count: int, description: string, arc_templates: list<string>, system_prompt_hint: string}
     */
    public static function get(string $genreKey): array
    {
        return match ($genreKey) {
            self::FANTASY_SCHOOL => [
                'books_count' => 7,
                'description' => 'Truyện trường học phép thuật, nhân vật chính trưởng thành qua từng năm, kẻ thù lớn và tình bạn.',
                'arc_templates' => [
                    'Khám phá thế giới mới và làm quen bạn bè',
                    'Bí mật mở ra, nguy hiểm rình rập',
                    'Trách nhiệm và hi sinh',
                    'Giải đấu và thử thách',
                    'Tổ chức bí mật và kháng chiến',
                    'Chiến trận và mất mát',
                    'Đối đầu cuối cùng và kết cục',
                ],
                'system_prompt_hint' => 'Write in the style of a young-adult fantasy serial: school years, magic, friendship, growing threat of a dark lord. One chapter per segment. Maintain continuity with previous chapters. Vietnamese or English as requested.',
            ],
            self::WUXIA => [
                'books_count' => 10,
                'description' => 'Võ hiệp: giang hồ, môn phái, nội công, ân oán và tình nghĩa.',
                'arc_templates' => [
                    'Vào đời giang hồ, gặp kỳ duyên',
                    'Học võ hoặc lĩnh ngộ bí kỹ',
                    'Kết oán với môn phái hoặc cao thủ',
                    'Tình bạn / tình thù chồng chất',
                    'Đại hội võ lâm hoặc tranh bá',
                    'Ân oán gia tộc / sư môn',
                    'Tình yêu và hiểu lầm',
                    'Đoàn tụ hoặc quy ẩn',
                    'Đại chiến hoặc hòa giải',
                    'Kết cục: thành danh hoặc quy ẩn',
                ],
                'system_prompt_hint' => 'Write in wuxia style: martial arts world, sects, internal energy (nei gong), chivalry, revenge and redemption, romance. One chapter. Maintain continuity. Prefer Vietnamese for dialogue and terms.',
            ],
            self::EMERGENT => [
                'books_count' => 7,
                'description' => 'Tự phát sinh từ world: thể loại và arc do mô phỏng (universe) quyết định.',
                'arc_templates' => [],
                'system_prompt_hint' => 'Write one chapter of a long-form serial. Tone and structure emerge from the linked world. Maintain continuity.',
            ],
            default => self::resolveGenesisOrFallback($genreKey),
        };
    }

    /**
     * Resolve genre_key as Genesis preset (25 loại truyện) or fallback to generic.
     *
     * @return array{books_count: int, description: string, arc_templates: list<string>, system_prompt_hint: string}
     */
    private static function resolveGenesisOrFallback(string $genreKey): array
    {
        $genesis = app(GenesisPresetService::class);
        $preset = $genesis->find($genreKey);
        if ($preset !== null) {
            return self::narrativeConfigFromGenesisPreset($preset);
        }
        return [
            'books_count' => self::DEFAULT_BOOKS_COUNT,
            'description' => 'Truyện dài kỳ đa arc, nhân vật phát triển qua nhiều tập.',
            'arc_templates' => self::GENRE_ARC_TEMPLATES['default'],
            'system_prompt_hint' => 'Write one chapter of a long-form serial. Maintain continuity with previous chapters.',
        ];
    }

    /**
     * Build narrative config from Genesis preset.
     * books_count: lấy từ preset['narrative_books_count'] nếu có, không thì theo genre (GENRE_BOOKS_COUNT).
     * arc_templates: lấy từ preset['narrative_arc_templates'] nếu có (array), không thì theo genre (GENRE_ARC_TEMPLATES).
     *
     * @param array{key: string, name: string, description: string, genre: string, narrative_books_count?: int, narrative_arc_templates?: list<string>} $preset
     * @return array{books_count: int, description: string, arc_templates: list<string>, system_prompt_hint: string}
     */
    private static function narrativeConfigFromGenesisPreset(array $preset): array
    {
        $genre = $preset['genre'] ?? 'default';
        $booksCount = isset($preset['narrative_books_count']) && is_int($preset['narrative_books_count'])
            ? $preset['narrative_books_count']
            : (self::GENRE_BOOKS_COUNT[$genre] ?? self::DEFAULT_BOOKS_COUNT);
        $arcTemplates = isset($preset['narrative_arc_templates']) && is_array($preset['narrative_arc_templates']) && $preset['narrative_arc_templates'] !== []
            ? $preset['narrative_arc_templates']
            : (self::GENRE_ARC_TEMPLATES[$genre] ?? self::GENRE_ARC_TEMPLATES['default']);
        $name = $preset['name'] ?? $preset['key'] ?? 'serial';
        $description = $preset['description'] ?? '';
        $hint = sprintf(
            'Write in the style of "%s": %s. One chapter. Maintain continuity. Vietnamese or English as requested.',
            $name,
            $description
        );
        return [
            'books_count' => $booksCount,
            'description' => $description,
            'arc_templates' => $arcTemplates,
            'system_prompt_hint' => $hint,
        ];
    }

    /** @return list<string> Legacy 3 + 25 Genesis preset keys. */
    public static function supportedGenres(): array
    {
        $legacy = [self::FANTASY_SCHOOL, self::WUXIA, self::EMERGENT];
        $genesisKeys = array_keys(app(GenesisPresetService::class)->all());
        return array_values(array_unique(array_merge($legacy, $genesisKeys)));
    }
}
