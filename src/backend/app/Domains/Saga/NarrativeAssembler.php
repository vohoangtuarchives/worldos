<?php

namespace App\Domains\Saga;

/**
 * NarrativeAssembler - Convert events into narrative text
 * 
 * Uses templates to generate varied, coherent narratives.
 */
class NarrativeAssembler
{
    /**
     * Assemble narrative from events.
     * 
     * @param array $events Detected events
     * @param int $epoch Current epoch
     * @return string Narrative text
     */
    public function assemble(array $events, int $epoch): string
    {
        // Convert Tick to Year (e.g. Tick 1 = Year 10, Tick 50 = Year 500)
        // Add random variance to year to make it feel organic
        $year = ($epoch * 10) + rand(0, 5); 
        
        if (empty($events)) {
            $template = $this->renderTemplate('default', ['severity' => 1]);
            return "Year {$year}: {$template}";
        }

        // Sort events by severity (highest first)
        usort($events, fn($a, $b) => ($b['severity'] ?? 0) <=> ($a['severity'] ?? 0));

        $narratives = [];
        $usedTemplates = [];

        foreach ($events as $event) {
            $templateKey = $event['narrative_template'] ?? 'default';
            
            // Avoid repeating same template type in one entry
            if (in_array($templateKey, $usedTemplates)) continue;
            
            $text = $this->renderTemplate($templateKey, $event);
            if (!in_array($text, $narratives)) {
                $narratives[] = $text;
                $usedTemplates[] = $templateKey;
            }
        }

        return "Year {$year}: " . implode(' ', $narratives);
    }

    /**
     * Render narrative template using Dictionary.
     */
    private function renderTemplate(string $template, array $event): string
    {
        $severity = $event['severity'] ?? 5;
        return \App\Domains\Saga\Services\NarrativeDictionary::getRandomTemplate($template, $severity);
    }
}
