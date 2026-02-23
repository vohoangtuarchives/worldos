<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service\Extractors;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\ChronicleEvent;

/**
 * HeroicEmergenceExtractor - Detects the birth of a Hero/Great Figure.
 */
final class HeroicEmergenceExtractor implements EventExtractor
{
    public function extract(CivilizationSnapshot $prev, CivilizationSnapshot $curr): array
    {
        $events = [];

        if ($curr->heroCount > $prev->heroCount) {
            $diff = $curr->heroCount - $prev->heroCount;
            $events[] = new ChronicleEvent(
                year: $curr->year,
                type: 'HERO_EMERGENCE',
                title: 'Anh Hùng Xuất Thế',
                description: 'Đứng trước dòng chảy thời đại, một hoặc nhiều vĩ nhân đã xuất hiện, bẻ cong quỹ đạo lịch sử bằng ý chí của họ.',
                severity: ChronicleEvent::SEVERITY_HIGH,
                metadata: [
                    'count' => $diff
                ]
            );
        }

        return $events;
    }
}
