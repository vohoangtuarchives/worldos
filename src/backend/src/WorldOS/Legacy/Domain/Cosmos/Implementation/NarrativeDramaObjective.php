<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmos\Implementation;

use WorldOS\Legacy\Domain\Cosmos\Contracts\Objective;
use WorldOS\Legacy\Domain\Cosmos\ValueObject\FitnessVector;
use WorldOS\Evolution\Domain\Legacy\Entity\Universe;
use WorldOS\Evolution\Domain\Legacy\Service\Fitness\CivilizationFitnessEvaluator;

/**
 * Objective mặc định tập trung vào tính tự sự (Drama/Story).
 * Hiện thực hóa logic đánh giá từ V4 nhưng chuyển đổi sang dạng Vector.
 */
final class NarrativeDramaObjective implements Objective
{
    private CivilizationFitnessEvaluator $civEvaluator;

    public function __construct(CivilizationFitnessEvaluator $civEvaluator)
    {
        $this->civEvaluator = $civEvaluator;
    }

    public function evaluate(Universe $universe, array $civilizations): FitnessVector
    {
        // 1. Stability (Độ ổn định - Ngược với entropy)
        $entropy = $universe->getCosmicState()->entropy;
        $maxEntropy = $universe->getLawGenome()->getMaxEntropy();
        $stability = max(0.01, 1.0 - abs($entropy - $maxEntropy));

        // 2. Complexity (Sự phức tạp - Dựa trên tech level và các sự kiện)
        $avgTech = 0.0;
        $totalCivs = count($civilizations);
        foreach ($civilizations as $civ) {
            $avgTech += $civ->getSnapshot()->technologicalLevel;
        }
        $avgTech = $totalCivs > 0 ? $avgTech / $totalCivs : 0.0;
        $complexity = min(1.0, $avgTech / 10.0 + ($universe->getYear() / 5000.0));

        // 3. Diversity (Sự đa dạng - Placeholder logic từ V4)
        $diversity = min(1.0, $totalCivs * 0.1); 

        // 4. Self-Reference (Heroism)
        $totalHeroes = 0;
        foreach ($civilizations as $civ) {
            $totalHeroes += $civ->getSnapshot()->heroCount;
        }
        $selfReference = min(1.0, $totalHeroes * 0.05);

        // 5. Coherence (Tính nhất quán - Thường cao ở giai đoạn đầu)
        $coherence = 1.0 - ($entropy * 0.5);

        // Thêm một chút nhiễu (stochastic noise) để mô phỏng sự sống
        $noise = fn() => (mt_rand() / mt_getrandmax() * 0.02);

        return new FitnessVector(
            stability: round($stability + $noise(), 4),
            complexity: round($complexity + $noise(), 4),
            diversity: round($diversity + $noise(), 4),
            selfReference: round($selfReference + $noise(), 4),
            coherence: round($coherence + $noise(), 4)
        );
    }

    public function getName(): string
    {
        return 'NarrativeDrama_v1';
    }
}
