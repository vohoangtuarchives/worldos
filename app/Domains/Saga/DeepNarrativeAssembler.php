<?php

namespace App\Domains\Saga;

use App\Domains\Saga\Services\NarrativeDictionary;
use Illuminate\Support\Str;

/**
 * DeepNarrativeAssembler - Fractal expansion of simulation events into long-form prose.
 * 
 * Goal: Transform 1 macro-event into 10-20 micro-beats to meet the 10k-20k words/chapter target.
 */
class DeepNarrativeAssembler
{
    private ?\App\Domains\Saga\Author\AuthorPersona $persona = null;
    private Services\ProseThesaurus $thesaurus;
    private Services\LedgerNarrator $ledgerNarrator;

    private array $descriptors = [
        'sensory' => [
            'famine_crisis' => [
                'Không khí mang vị của cát bụi và đất khô cằn.',
                'Một sự im lặng rỗng tuếch bao trùm những cánh đồng nơi chim chóc không còn hót.',
                'Mùi ngũ cốc cháy nắng, đắng ngắt và chết chóc, bám chặt vào làn gió.',
                'Bụng đói cồn cào trong một bản đồng ca tuyệt vọng vang vọng khắp mọi ngưỡng cửa.',
                'Những gương mặt hốc hác, ánh mắt lờ đờ nhìn vào hư vô như tìm kiếm một phép màu.',
                'Tiếng lá khô xào xạc trên nền đất nứt nẻ nghe như tiếng thở dài của đại địa.',
            ],
            'social_tension' => [
                'Vị kim loại của sắt thép rút khỏi bao lấp đầy không khí ẩm ướt.',
                'Những bóng đen trong hẻm dường như đập theo một nhịp điệu ẩn giấu, giận dữ.',
                'Tiếng la hét từ quảng trường chợ là một âm thanh răng cưa, xé toạc sự bình yên.',
                'Mỗi ánh nhìn đều sắc lẹm, một lưỡi đao thầm lặng thử thách lòng kiên định của người khác.',
                'Tiếng bước chân dồn dập trên đá tảng, báo hiệu một cơn bão lòng người sắp ập tới.',
                'Mùi mồ hôi và sự lo âu nồng nặc trong từng ngóc ngách của thành phố.',
            ],
            'default' => [
                'Tiếng chuông chùa xa xăm vang vọng trong buổi hoàng hôn tĩnh lặng.',
                'Mây trắng lững lờ trôi, phản chiếu ánh bình minh rạng rỡ.',
                'Hương thơm của hoa cỏ dại thoang thoảng trong làn gió nhẹ.',
            ]
        ],
        'atmosphere' => [
            'famine_crisis' => [
                'Một nỗi tuyệt vọng nặng nề, trì trệ bao trùm lấy ngôi làng.',
                'Chân trời dường như xa hơn, như thể chính thế giới đang rút lui.',
                'Thời gian kéo dài mỏng manh, chỉ được đo bằng ánh sáng mờ dần trong đôi mắt yếu ớt.',
                'Cái bóng của thần chết dường như dài hơn sau mỗi buổi chiều tà.',
            ],
            'social_tension' => [
                'Căng thẳng là một trọng lượng vật lý, đè nặng lên vai mỗi người qua đường.',
                'Thành phố như một thùng thuốc súng, chờ đợi một tia lửa duy nhất để thắp sáng màn đêm.',
                'Lòng trung thành thay đổi như cát trong cơn bão, không thể đoán trước và mài mòn.',
                'Bầu không khí đặc quánh sự nghi kỵ, ngay cả hơi thở cũng thấy nặng nề.',
            ],
            'default' => [
                'Thế giới dường như đang nín thở chờ đợi một điều gì đó vĩ đại.',
                'Sự tĩnh lặng bao trùm khắp đại địa, báo hiệu một kỷ nguyên yên bình.',
            ]
        ]
    ];

    public function __construct(Services\LedgerNarrator $ledgerNarrator)
    {
        $this->thesaurus = new Services\ProseThesaurus();
        $this->ledgerNarrator = $ledgerNarrator;
    }

    public function setPersona(\App\Domains\Saga\Author\AuthorPersona $persona): void
    {
        $this->persona = $persona;
    }

    /**
     * Assemble narrative from events with deep expansion.
     */
    public function assemble(array $events, int $epoch): string
    {
        $year = ($epoch * 10) + rand(0, 5);
        
        if (empty($events)) {
            $events = [['type' => 'default', 'severity' => 2, 'narrative_template' => 'default']];
        }

        // Sort events by severity
        usort($events, fn($a, $b) => ($b['severity'] ?? 0) <=> ($a['severity'] ?? 0));

        $chapters = [];
        foreach ($events as $event) {
            $chapters[] = $this->expandEventIntoScene($event, $epoch);
        }

        $prose = "Năm {$year}:\n\n" . implode("\n\n", $chapters);

        // Apply Author Persona Stylization if exists
        if ($this->persona) {
            $prose = $this->persona->stylize($prose);
        }

        return $prose;
    }

    /**
     * Recursive Expansion: Macro Event -> meso Beats -> micro Prose
     */
    private function expandEventIntoScene(array $event, int $epoch): string
    {
        $type = $event['type'] ?? 'default';
        $severity = $event['severity'] ?? 5;
        $beats = [];

        // Special Handling for Ledger/Epic Events
        if (($event['type'] ?? '') === 'ledger_event' && isset($event['original_event'])) {
            // Delegate to LedgerNarrator for the core description
            $epicDescription = $this->ledgerNarrator->narrate($event['original_event']);
            if ($epicDescription) {
                return $this->stylizeEpic($epicDescription);
            }
        }

        // 1. Introduction Beat: Setting the stage
        $beats[] = $this->generateIntroBeat($type, $severity);

        // 2. Sensory Beat: immersive details
        $beats[] = $this->generateSensoryBeat($type);

        // 3. Environmental Beat: How the world reacts
        $beats[] = $this->generateEnvironmentalBeat($type);

        // 4. Perspective Beat: A "Character-view" of the event
        $beats[] = $this->generatePerspectiveBeat($event);

        // 5. Outcome Beat: Concrete result
        if ($type === 'collapse_warning' && !empty($event['description'])) {
            $beats[] = $event['description'];
        } else {
            $outcomeBase = NarrativeDictionary::getRandomTemplate($type, $severity);
            $beats[] = $outcomeBase;
        }

        // 6. Signature Flourish (Randomly inject author's 'voice')
        if ($this->persona && rand(1, 100) <= 40) {
            $flourish = $this->persona->getRandomFlourish();
            if ($flourish) $beats[] = $flourish;
        }

        return $this->refineAndExpand($beats, $severity);
    }

    private function generateIntroBeat(string $type, int $severity): string
    {
        if ($this->persona && !empty($this->persona->introStyles)) {
            return $this->persona->introStyles[array_rand($this->persona->introStyles)];
        }

        $base = NarrativeDictionary::getRandomTemplate($type, $severity);
        $prefix = [
            "Lúc bấy giờ, khi vận mệnh xoay vần,",
            "Trong những ngày u tối của kỷ nguyên thứ $severity,",
            "Trời đất dường như cảm nhận được sự thay đổi sắp tới,",
            "Giữa dòng chảy vô tận của thời gian,",
            "Khi những ngôi sao bắt đầu kể câu chuyện về sự diệt vong,",
        ];
        return $prefix[array_rand($prefix)] . " " . $base;
    }

    private function generateSensoryBeat(string $type): string
    {
        // 1. Try Author-specific sensory pool
        if ($this->persona && isset($this->persona->descriptors['sensory'][$type])) {
            $pool = $this->persona->descriptors['sensory'][$type];
            return $pool[array_rand($pool)];
        }

        // 2. Fallback to global pool
        $pool = $this->descriptors['sensory'][$type] ?? $this->descriptors['sensory']['default'];
        return $pool[array_rand($pool)];
    }

    private function generateEnvironmentalBeat(string $type): string
    {
        // 1. Try Author-specific atmosphere pool
        if ($this->persona && isset($this->persona->descriptors['atmosphere'][$type])) {
            $pool = $this->persona->descriptors['atmosphere'][$type];
            return $pool[array_rand($pool)];
        }

        // 2. Fallback to global pool
        $pool = $this->descriptors['atmosphere'][$type] ?? $this->descriptors['atmosphere']['default'];
        return $pool[array_rand($pool)];
    }

    private function generatePerspectiveBeat(array $event): string
    {
        $perspectives = [
            "Một người lính canh trên thành cao thở dài, mắt nhìn về phía chân trời xa xăm.",
            "Kẻ hành khất run rẩy trong bóng tối, ôm lấy chút hy vọng cuối cùng.",
            "Tại thư viện đại đế quốc, các học giả vội vã ghi chép lại những điềm báo kỳ lạ.",
            "Một đứa trẻ ngơ ngác hỏi mẹ về sự thay đổi của bầu trời.",
            "Lão phu nhân ngồi bên bậc cửa, lần tràng hạt và cầu nguyện cho một sự bình an giả tạo.",
            "Người thợ rèn ngừng tay búa, lắng nghe tiếng gió mang theo những điềm báo không lành.",
        ];
        return $perspectives[array_rand($perspectives)];
    }

    /**
     * Refine and Expand with Lexicon/Thesaurus support.
     */
    private function refineAndExpand(array $beats, int $severity): string
    {
        $prose = implode(" ", $beats);
        
        // Determine style based on persona or default to literary
        $style = 'literary';
        if ($this->persona) {
            $style = match($this->persona->tone) {
                'grand_oriental' => 'han_viet',
                'cynical_dark' => 'literary',
                default => 'literary'
            };
        }

        // Use Thesaurus to enrich the text
        $prose = $this->thesaurus->enrich($prose, $style, 70); // 70% density for high variety

        // Add bridging sentences for volume
        $bridges = $this->persona && !empty($this->persona->bridgingPhrases) 
            ? $this->persona->bridgingPhrases 
            : [
                "Không một ai có thể đứng ngoài vòng xoáy này.",
                "Vận mệnh đã được định đoạt từ thuở sơ khai.",
                "Mỗi bước đi đều để lại những dấu ấn không thể phai mờ trên dòng lịch sử.",
                "Sức mạnh của thời đại cuộn trào như thác đổ.",
                "Sự thật bị che lấp bởi lớp bụi mờ của năm tháng.",
                "Trong cái tĩnh lặng đó, một hạt giống của sự biến đổi đang nảy mầm.",
            ];
        
        $prose .= " " . $bridges[array_rand($bridges)];

        return $prose;
    }

    private function stylizeEpic(string $text): string
    {
        if ($this->persona) {
            return $this->persona->stylize($text);
        }
        return $text;
    }
}
