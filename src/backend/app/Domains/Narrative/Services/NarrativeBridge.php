<?php

namespace App\Domains\Narrative\Services;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Narrative\Entities\GenreSignature;
use App\Domains\Narrative\Entities\GenreVector;

/**
 * Cầu nối từ World State (mô phỏng) sang narrative.
 * Cung cấp từ vựng đa dạng và nhận diện tình huống để biên niên sử không nghèo nàn.
 * Supports 3-layer pipeline: SituationDetector → SemanticMapper → ChronicleRenderer (optional).
 */
class NarrativeBridge
{
    /** Các cách diễn đạt theo tình huống — nhiều biến thể để tránh lặp */
    private const PHRASINGS = [
        'inequality_high' => [
            'Khoảng cách giữa kẻ có và không có trở nên vực thẳm; tiếng gọi cách mạng vang lên khắp phố.',
            'Bất bình đẳng đã vượt ngưỡng chịu đựng; xã hội rạn nứt giữa thiểu số nắm quyền và đám đông bị bỏ rơi.',
            'Sự phân tầng cực đoan khiến công lý chỉ còn là từ ngữ; người dân bắt đầu nhìn về bạo lực như lối thoát.',
        ],
        'trauma_high' => [
            'Thế giới mang vết thương từ quá khứ; hoài nghi và sợ hãi trở thành trạng thái mặc định.',
            'Nỗi đau tập thể chưa bao giờ lành; mỗi thế hệ kế thừa sự tê liệt và ám ảnh.',
            'Ký ức tai ương in sâu vào văn hóa; niềm tin vào tương lai trở nên xa xỉ.',
        ],
        'resource_scarce' => [
            'Khan hiếm tài nguyên và nạn đói đẩy con người vào tuyệt vọng; mọi thỏa thuận xã hội bị thử thách.',
            'Nguồn sống cạn kiệt; tranh giành từng phần nhỏ trở thành quy luật sinh tồn.',
            'Thời kỳ thiếu thốn kéo dài; đạo đức và trật tự nhường chỗ cho bản năng sinh tồn.',
        ],
        'resource_abundant' => [
            'Thời kỳ dư dật vật chất, dù phân phối chưa hẳn công bằng.',
            'Sự giàu có vật chất đạt mức chưa từng thấy; câu hỏi về công bằng vẫn ám ảnh.',
            'Kỷ nguyên sung túc; nhưng ai được hưởng và ai bị bỏ lại vẫn là vết nứt không lành.',
        ],
        'elite_corrupt' => [
            'Một thiểu số thống trị tham nhũng nắm quyền tuyệt đối; pháp luật chỉ là công cụ trấn áp.',
            'Giới tinh hoa cấu kết, cai trị bằng sự bất chấp; người dân không còn kênh phản kháng.',
            'Chế độ đầu sỏ thao túng mọi thể chế; công lý và minh bạch chỉ còn trên giấy.',
        ],
        'elite_fractured' => [
            'Giai cấp thống trị rạn nứt; nội chiến giữa các phe phái tinh hoa đang cận kề.',
            'Giới cầm quyền chia rẽ sâu sắc; mỗi phe kéo lực lượng và chuẩn bị đối đầu.',
            'Sự đoàn kết của tinh hoa tan vỡ; tranh giành quyền lực có thể kéo cả thế giới vào hỗn loạn.',
        ],
        'innovation_high' => [
            'Công nghệ và tư tưởng bùng nổ; mọi chuẩn mực cũ bị chất vấn.',
            'Làn sóng đổi mới thay đổi từng ngày; cơ hội và rủi ro cùng tăng theo cấp số nhân.',
            'Sáng tạo trở thành động lực chính; nhưng tốc độ thay đổi vượt quá khả năng thích ứng của nhiều người.',
        ],
        'order_high_entropy_low' => [
            'Trật tự thống trị; entropy bị kìm hãm — nhưng cái giá là sự cứng nhắc và đàn áp khác biệt.',
            'Xã hội được tổ chức chặt chẽ; mọi thứ có vị trí — và ai đi lệch sẽ bị loại trừ.',
            'Trạng thái ổn định gần như tĩnh tại; ít biến động nhưng cũng ít không gian cho tự do.',
        ],
        'entropy_high' => [
            'Entropy lan rộng; cấu trúc và dự đoán dần nhường chỗ cho hỗn mang.',
            'Trật tự sụp đổ từng mảng; thế giới tiến gần đến trạng thái mà mọi quy luật đều mờ nhạt.',
            'Sự hỗn loạn trở thành chuẩn mực; ranh giới giữa có thể và không thể ngày càng mong manh.',
        ],
        'military_high_cohesion_low' => [
            'Bạo lực và chiến tranh trở thành ngôn ngữ chính; đoàn kết xã hội chỉ còn trong tuyên truyền.',
            'Xung đột vũ trang định hình đời sống; người dân sống trong bóng tối của chiến tranh và chia rẽ.',
            'Quân đội và xung đột thống trị; hòa bình và đồng thuận là thứ xa xỉ.',
        ],
        'stagnation_risk' => [
            'Đổi mới gần như dừng lại; thế giới như đóng băng trong một trạng thái bất biến.',
            'Sự tĩnh tại trở thành mối đe dọa; thiếu thay đổi đồng nghĩa với cái chết từ từ.',
            'Trật tự quá cao, entropy quá thấp; hệ thống trở nên giòn và không còn khả năng thích nghi.',
        ],
        'pressure_critical' => [
            'Áp lực tích tụ vượt ngưỡng; một cú sụp đổ hoặc tái cấu trúc lớn có thể xảy ra bất kỳ lúc nào.',
            'Mâu thuẫn và căng thẳng đạt đến điểm tới hạn; hệ thống đứng trước ngã ba lịch sử.',
            'Sự chịu đựng của cấu trúc xã hội sắp cạn; chỉ cần một kích hoạt nhỏ.',
        ],
        'neutral' => [
            'Các lực lượng cân bằng tạm thời; không rõ thế giới sẽ trôi về trật tự hay hỗn mang.',
            'Trạng thái chuyển tiếp; nhiều khả năng vẫn mở.',
        ],
    ];

    /** Mở đầu theo genre — đa dạng văn phong */
    private const GENRE_INTROS = [
        'utopian' => [
            'Một thực tại **utopia** — hòa bình, ổn định, tiến bộ và tương đối công bằng.',
            'Thế giới này tiệm cận **utopia**: trật tự, gắn kết và công lý được đặt lên cao.',
        ],
        'dystopian' => [
            'Một thực tại **dystopia** — đàn áp, giám sát, bất bình và tầng lớp hóa sâu.',
            'Xã hội mang đậm **dystopia**: quyền lực tập trung, bất bình đẳng cùng cực và kháng cự bị đè bẹp.',
        ],
        'cyberpunk' => [
            'Sắc thái **cyberpunk** — công nghệ cao, đời sống thấp, tập đoàn thống trị và bóng tối neon.',
            'Thế giới **cyberpunk**: đổi mới công nghệ và bất bình đẳng cùng tồn tại trong một không gian đầy mâu thuẫn.',
        ],
        'eldritch' => [
            'Một thực tại **eldritch** — điên rồ, phi Euclid, vực thẳm và ám ảnh.',
            'Bầu không khí **eldritch** bao trùm; ranh giới giữa thực và ảo, hợp lý và không thể hiểu nổi ngày càng mờ.',
        ],
        'war' => [
            'Thế giới **chiến tranh** — xung đột, tàn phá và cả những huyền thoại về vinh quang.',
            'Chiến tranh và bạo lực định nghĩa kỷ nguyên này; đoàn kết chỉ còn trong huyền thoại.',
        ],
        'decay' => [
            'Sắc thái **suy tàn** — đổ nát, phai nhạt và hoài niệm về những gì đã mất.',
            'Thế giới trong **decay**: đổi mới tắt lịm, trật tự vỡ vụn; chỉ còn ký ức về thời vàng son.',
        ],
        'neutral' => [
            'Một thực tại **cân bằng**, với nhiều lực đẩy và kéo chưa phân thắng bại.',
        ],
    ];

    /**
     * Nhận diện các tình huống từ state (phù hợp mô hình simulate: state → situations).
     * Mỗi phần tử: ['key' => ..., 'intensity' => 0..1, 'phrase' => one of PHRASINGS].
     * When seed is provided, phrase selection is deterministic.
     */
    public function getSituations(WorldStateVector $vector, ?string $seed = null): array
    {
        $situations = [];
        $pick = function (array $arr, string $s) use ($seed) {
            return $seed !== null ? self::pickWithSeed($arr, $s) : $arr[array_rand($arr)];
        };
        $s = $seed ?? '';
        if ($vector->getInequality() > 0.7) {
            $situations[] = ['key' => 'inequality_high', 'intensity' => $vector->getInequality(), 'phrase' => $pick(self::PHRASINGS['inequality_high'], $s . 'inequality_high')];
        }
        if ($vector->getTrauma() > 0.6) {
            $situations[] = ['key' => 'trauma_high', 'intensity' => $vector->getTrauma(), 'phrase' => $pick(self::PHRASINGS['trauma_high'], $s . 'trauma_high')];
        }
        if ($vector->getResourceStock() < 0.2) {
            $situations[] = ['key' => 'resource_scarce', 'intensity' => 1.0 - $vector->getResourceStock(), 'phrase' => $pick(self::PHRASINGS['resource_scarce'], $s . 'resource_scarce')];
        } elseif ($vector->getResourceStock() > 0.8) {
            $situations[] = ['key' => 'resource_abundant', 'intensity' => $vector->getResourceStock(), 'phrase' => $pick(self::PHRASINGS['resource_abundant'], $s . 'resource_abundant')];
        }
        if ($vector->getEliteCohesion() > 0.8 && $vector->getLegitimacy() < 0.3) {
            $situations[] = ['key' => 'elite_corrupt', 'intensity' => 0.9, 'phrase' => $pick(self::PHRASINGS['elite_corrupt'], $s . 'elite_corrupt')];
        } elseif ($vector->getEliteCohesion() < 0.3) {
            $situations[] = ['key' => 'elite_fractured', 'intensity' => 1.0 - $vector->getEliteCohesion(), 'phrase' => $pick(self::PHRASINGS['elite_fractured'], $s . 'elite_fractured')];
        }
        if ($vector->getInnovation() > 0.7) {
            $situations[] = ['key' => 'innovation_high', 'intensity' => $vector->getInnovation(), 'phrase' => $pick(self::PHRASINGS['innovation_high'], $s . 'innovation_high')];
        }
        if ($vector->getOrder() > 0.7 && $vector->getEntropy() < 0.3) {
            $situations[] = ['key' => 'order_high_entropy_low', 'intensity' => $vector->getOrder(), 'phrase' => $pick(self::PHRASINGS['order_high_entropy_low'], $s . 'order_high_entropy_low')];
        }
        if ($vector->getEntropy() > 0.75) {
            $situations[] = ['key' => 'entropy_high', 'intensity' => $vector->getEntropy(), 'phrase' => $pick(self::PHRASINGS['entropy_high'], $s . 'entropy_high')];
        }
        if ($vector->getMilitary() > 0.7 && $vector->getCohesion() < 0.5) {
            $situations[] = ['key' => 'military_high_cohesion_low', 'intensity' => $vector->getMilitary(), 'phrase' => $pick(self::PHRASINGS['military_high_cohesion_low'], $s . 'military_high_cohesion_low')];
        }
        if ($vector->getOrder() > 0.85 && $vector->getEntropy() < 0.15 && $vector->getInnovation() < 0.2) {
            $situations[] = ['key' => 'stagnation_risk', 'intensity' => 0.8, 'phrase' => $pick(self::PHRASINGS['stagnation_risk'], $s . 'stagnation_risk')];
        }
        $contradiction = $vector->getInequality() * (1.0 - $vector->getLegitimacy()) * 0.4
            + $vector->getTrauma() * 0.35 + $vector->getEntropy() * 0.25;
        if ($contradiction > 0.7) {
            $situations[] = ['key' => 'pressure_critical', 'intensity' => $contradiction, 'phrase' => $pick(self::PHRASINGS['pressure_critical'], $s . 'pressure_critical')];
        }
        if (empty($situations)) {
            $situations[] = ['key' => 'neutral', 'intensity' => 0.5, 'phrase' => $pick(self::PHRASINGS['neutral'], $s . 'neutral')];
        }

        return $situations;
    }

    /**
     * Ngữ cảnh giàu cho biên niên: genre + traits + danh sách tình huống (để LLM hoặc template dùng).
     * When seed is provided, phrase selection is deterministic.
     */
    public function getRichContextForChronicle(WorldStateVector $vector, ?string $seed = null): array
    {
        $genre = $this->detectGenre($vector);
        $situations = $this->getSituations($vector, $seed);
        $introPool = self::GENRE_INTROS[$genre->getPrimaryGenre()] ?? self::GENRE_INTROS['neutral'];
        $intro = $seed !== null
            ? self::pickWithSeed($introPool, $seed)
            : $introPool[array_rand($introPool)];

        $genreVector = $this->getGenreVector($vector);
        return [
            'genre' => $genre->getPrimaryGenre(),
            'genre_vector' => $genreVector->getScores(),
            'traits' => $genre->getTraits(),
            'intro_phrase' => $intro,
            'situations' => $situations,
            'order' => $vector->getOrder(),
            'entropy' => $vector->getEntropy(),
            'innovation' => $vector->getInnovation(),
            'cohesion' => $vector->getCohesion(),
            'legitimacy' => $vector->getLegitimacy(),
            'inequality' => $vector->getInequality(),
        ];
    }

    /**
     * Giữ tương thích cũ; nội dung đã đa dạng hơn nhờ situations.
     * Pass seed for deterministic output.
     */
    public function generateContext(WorldStateVector $vector, ?string $seed = null): string
    {
        $rich = $this->getRichContextForChronicle($vector, $seed);
        $parts = [$rich['intro_phrase']];

        $used = [];
        foreach ($rich['situations'] as $s) {
            if ($s['key'] === 'neutral' && count($rich['situations']) > 1) {
                continue;
            }
            if (!in_array($s['phrase'], $used, true)) {
                $parts[] = $s['phrase'];
                $used[] = $s['phrase'];
            }
        }

        return implode(' ', $parts);
    }

    public function detectGenre(WorldStateVector $vector): GenreSignature
    {
        if ($vector->getOrder() > 0.7 && $vector->getCohesion() > 0.7 && $vector->getEntropy() < 0.3 && $vector->getInequality() < 0.3) {
            return new GenreSignature(GenreSignature::GENRE_UTOPIAN, ['peaceful', 'stable', 'advanced', 'equitable']);
        }
        if ($vector->getOrder() > 0.7 && ($vector->getLegitimacy() < 0.4 || $vector->getInequality() > 0.8)) {
            return new GenreSignature(GenreSignature::GENRE_DYSTOPIAN, ['oppressive', 'surveillance', 'resistance', 'stratified']);
        }
        if ($vector->getInnovation() > 0.8 && $vector->getInequality() > 0.6) {
            return new GenreSignature(GenreSignature::GENRE_CYBERPUNK, ['high-tech', 'low-life', 'corporate-rule', 'neon-gloom']);
        }
        if ($vector->getEntropy() > 0.9 || $vector->getTrauma() > 0.8) {
            return new GenreSignature(GenreSignature::GENRE_ELDRITCH, ['madness', 'non-euclidean', 'abyssal', 'haunted']);
        }
        if ($vector->getMilitary() > 0.7 && $vector->getCohesion() < 0.5) {
            return new GenreSignature(GenreSignature::GENRE_WAR, ['conflict', 'devastation', 'glory']);
        }
        if ($vector->getInnovation() < 0.2 && $vector->getOrder() < 0.3) {
            return new GenreSignature(GenreSignature::GENRE_DECAY, ['ruin', 'fading', 'nostalgia']);
        }
        return new GenreSignature('neutral', ['balanced']);
    }

    /**
     * Genre as vector of scores (0..1) for narrative layer; primary = argmax.
     */
    public function getGenreVector(WorldStateVector $vector): GenreVector
    {
        $order = $vector->getOrder();
        $cohesion = $vector->getCohesion();
        $entropy = $vector->getEntropy();
        $inequality = $vector->getInequality();
        $legitimacy = $vector->getLegitimacy();
        $innovation = $vector->getInnovation();
        $military = $vector->getMilitary();
        $trauma = $vector->getTrauma();

        $utopian = max(0, min(1, ($order * 0.3 + $cohesion * 0.3 + (1 - $entropy) * 0.2 + (1 - $inequality) * 0.2)));
        $dystopian = max(0, min(1, ($order * 0.3 + (1 - $legitimacy) * 0.4 + $inequality * 0.3)));
        $cyberpunk = max(0, min(1, ($innovation * 0.5 + $inequality * 0.5)));
        $eldritch = max(0, min(1, ($entropy * 0.5 + $trauma * 0.5)));
        $war = max(0, min(1, ($military * 0.6 + (1 - $cohesion) * 0.4)));
        $decay = max(0, min(1, ((1 - $innovation) * 0.5 + (1 - $order) * 0.5)));
        $neutral = 0.2;

        return new GenreVector([
            'utopian' => $utopian,
            'dystopian' => $dystopian,
            'cyberpunk' => $cyberpunk,
            'eldritch' => $eldritch,
            'war' => $war,
            'decay' => $decay,
            'neutral' => $neutral,
        ]);
    }

    /**
     * Deterministic phrase pick from array using seed (e.g. worldId + age).
     */
    public static function pickWithSeed(array $arr, string $seed): string
    {
        if (empty($arr)) {
            return '';
        }
        $idx = abs(crc32($seed)) % count($arr);
        return $arr[array_keys($arr)[$idx]];
    }

    /**
     * Sinh một đoạn biên niên từ template (không cần LLM), dựa hoàn toàn trên state — phù hợp mô hình simulate.
     * Pass seed (e.g. universeId . ':' . $age) for deterministic phrase choice.
     */
    public function buildChronicleParagraph(WorldStateVector $vector, int $age, ?string $seed = null): string
    {
        $seed = $seed ?? ((string) $age);
        $rich = $this->getRichContextForChronicle($vector, $seed);
        $sentences = [];
        $sentences[] = "Ở chu kỳ thứ {$age}, " . str_replace('**', '', $rich['intro_phrase']);

        $maxSituations = 3;
        $count = 0;
        foreach ($rich['situations'] as $s) {
            if ($count >= $maxSituations) {
                break;
            }
            if ($s['key'] === 'neutral' && count($rich['situations']) > 1) {
                continue;
            }
            $sentences[] = $s['phrase'];
            $count++;
        }

        $sentences[] = "Trạng thái mô phỏng: entropy " . round($vector->getEntropy(), 2) . ", trật tự " . round($vector->getOrder(), 2) . ", đổi mới " . round($vector->getInnovation(), 2) . ".";
        return implode(' ', $sentences);
    }
}
