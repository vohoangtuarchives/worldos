<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Legacy;

use WorldOS\Chronicle\Domain\Service\HistorianService;
use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;

/**
 * NarrativeAssembler - Convert events into narrative text
 * 
 * Uses HistorianService to synthesize events into Eras for a cohesive history.
 */
class NarrativeAssembler
{
    public function __construct(
        private readonly HistorianService $historian,
    ) {
    }

    /**
     * Assemble a full historical chronicle from events.
     * 
     * @param ChronicleEvent[] $events
     * @param int|null         $upToTick
     */
    public function assembleChronicle(array $events, ?int $upToTick = null): string
    {
        $eras = $this->historian->synthesizeEras($events, $upToTick);
        $output = [];

        foreach ($eras as $era) {
            $output[] = "--- {$era->name} ({$era->startTick} - {$era->endTick}) ---";
            $output[] = $era->description;
            $output[] = "";
        }

        return implode("\n", $output);
    }

    /**
     * Legacy assemble narrative from raw event arrays.
     */
    public function assemble(array $events, int $epoch): string
    {
        $year = ($epoch * 10) + rand(0, 5); 
        
        if (empty($events)) {
            $template = $this->renderTemplate('default', ['severity' => 1]);
            return "Year {$year}: {$template}";
        }

        usort($events, fn($a, $b) => ($b['severity'] ?? 0) <=> ($a['severity'] ?? 0));

        $narratives = [];
        $usedTemplates = [];

        foreach ($events as $event) {
            $templateKey = $event['narrative_template'] ?? 'default';
            if (in_array($templateKey, $usedTemplates)) continue;
            
            $text = $this->renderTemplate($templateKey, $event);
            if (!in_array($text, $narratives)) {
                $narratives[] = $text;
                $usedTemplates[] = $templateKey;
            }
        }

        return "Year {$year}: " . implode(' ', $narratives);
    }

    private function renderTemplate(string $template, array $event): string
    {
        $severity = $event['severity'] ?? 5;
        // This remains legacy for now until NarrativeDictionary is refactored
        return \WorldOS\Legacy\Application\Saga\Services\NarrativeDictionary::getRandomTemplate($template, $severity);
    }
}
