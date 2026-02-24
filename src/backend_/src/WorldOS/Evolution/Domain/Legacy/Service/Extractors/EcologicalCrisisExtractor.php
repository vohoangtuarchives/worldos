<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service\Extractors;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\ValueObject\ChronicleEvent;

/**
 * EcologicalCrisisExtractor - Detects structural or economic collapse.
 */
final class EcologicalCrisisExtractor implements EventExtractor
{
    public function extract(CivilizationSnapshot $prev, CivilizationSnapshot $curr): array
    {
        $events = [];

        if ($curr->structuralEntropy > 0.8 && $prev->structuralEntropy <= 0.8) {
            $events[] = new ChronicleEvent(
                year: $curr->year,
                type: 'STRUCTURAL_CRISIS',
                title: 'Khủng Hoảng Thể Chế',
                description: 'Cấu trúc quan liêu đã đạt đến mức giới hạn. Thể chế mục ruỗng không còn khả năng tự sửa lỗi.',
                severity: ChronicleEvent::SEVERITY_CRITICAL
            );
        }

        if ($curr->prosperity < ($prev->prosperity - 0.1)) {
            $events[] = new ChronicleEvent(
                year: $curr->year,
                type: 'ECONOMIC_COLLAPSE',
                title: 'Suy Thoái Diện Rộng',
                description: 'Khủng hoảng tài nguyên và hạ tầng khiến nền kinh tế sụp đổ dữ dội.',
                severity: ChronicleEvent::SEVERITY_HIGH
            );
        }
        
        if ($curr->legitimacy < 0.2 && $prev->legitimacy >= 0.2) {
            $events[] = new ChronicleEvent(
                year: $curr->year,
                type: 'LEGITIMACY_LOSS',
                title: 'Mất Lòng Dân',
                description: 'Giai cấp thống trị mất đi tính chính danh trầm trọng. Xã hội bên bờ vực nổi dậy.',
                severity: ChronicleEvent::SEVERITY_HIGH
            );
        }

        return $events;
    }
}
