<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service\Extractors;

use Tuzy\Domain\Evolution\ValueObject\CivilizationSnapshot;
use Tuzy\Domain\Evolution\ValueObject\ChronicleEvent;

/**
 * FactionDynamicsExtractor - Detects faction splits and merges.
 */
final class FactionDynamicsExtractor implements EventExtractor
{
    public function extract(CivilizationSnapshot $prev, CivilizationSnapshot $curr): array
    {
        $events = [];

        $prevIds = array_map(fn($f) => $f->id, $prev->factions);
        $currIds = array_map(fn($f) => $f->id, $curr->factions);

        $newIds = array_diff($currIds, $prevIds);

        $splitLogged = false;
        $mergeLogged = false;

        foreach ($newIds as $id) {
            if (str_ends_with($id, '_a') || str_ends_with($id, '_b')) {
                if (!$splitLogged) {
                    $events[] = new ChronicleEvent(
                        year: $curr->year,
                        type: 'FACTION_SPLIT',
                        title: 'Rạn Nứt Nội Bộ',
                        description: 'Căng thẳng nội tại và áp lực phân cực đã khiến một phe phái lớn bị chia cắt thành các nhánh đối nghịch.',
                        severity: ChronicleEvent::SEVERITY_HIGH
                    );
                    $splitLogged = true;
                }
            } elseif (str_ends_with($id, '_merged')) {
                if (!$mergeLogged) {
                    $events[] = new ChronicleEvent(
                        year: $curr->year,
                        type: 'FACTION_MERGE',
                        title: 'Liên Minh Quyền Lực',
                        description: 'Trước áp lực sinh tồn, các thế lực chính trị đã gạt bỏ bất đồng để sáp nhập thành một liên minh lớn hơn.',
                        severity: ChronicleEvent::SEVERITY_MEDIUM
                    );
                    $mergeLogged = true;
                }
            }
        }

        return $events;
    }
}
