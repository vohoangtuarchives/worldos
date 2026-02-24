<?php

namespace WorldOS\Saga\Domain\Legacy;

use WorldOS\Legacy\Infrastructure\Power\Repositories\WorldEventLedgerRepository;

class LegendInjector
{
    public function __construct(
        private WorldEventLedgerRepository $ledgerRepository
    ) {}

    public function inject(string $worldId, array &$promptCapsule): void
    {
        $history = $this->ledgerRepository->getHistory($worldId, 5);
        
        $legends = [];
        foreach ($history as $event) {
            if ($event->visibility === 'public') {
                $legends[] = $this->formatEventAsLegend($event);
            }
        }

        if (!empty($legends)) {
            $promptCapsule['world_legends'] = $legends;
            $promptCapsule['narrative_instruction'] .= "\n- Tham chiếu đến các sự kiện lịch sử (legends) sau để tạo độ sâu thế giới.";
        }
    }

    private function formatEventAsLegend(object $event): string
    {
        // Mapping type + magnitude to a human-readable legend snippet
        return "Sự kiện " . $event->event_type . " tại thời kỳ " . $event->epoch . " đã để lại dấu ấn vĩnh viễn.";
    }
}
