<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Narrative\Services;

use App\Models\StoryBible;

/**
 * Resolve Lorebook-style facts for a chapter: locations, factions (from worldbuilding_rules).
 * Optionally filter by arc one_line (activation: include entities whose name appears in arc).
 */
class LorebookResolver
{
    private const MAX_LOCATIONS = 5;
    private const MAX_FACTIONS = 5;

    /**
     * Return facts to inject into chronicleContext: lorebook_locations, lorebook_factions.
     * When arcOneLine is provided, prefer entities whose name appears in the arc line.
     *
     * @return array{lorebook_locations?: list<array>, lorebook_factions?: list<array>}
     */
    public function resolveForChapter(string $seriesId, string $arcOneLine = ''): array
    {
        $bible = StoryBible::where('narrative_series_id', $seriesId)->first();
        if ($bible === null || !is_array($bible->worldbuilding_rules)) {
            return [];
        }
        $rules = $bible->worldbuilding_rules;
        $arcLower = mb_strtolower($arcOneLine);

        $locations = $this->resolveEntities(
            $rules['locations'] ?? [],
            $arcLower,
            self::MAX_LOCATIONS
        );
        $factions = $this->resolveEntities(
            $rules['factions'] ?? [],
            $arcLower,
            self::MAX_FACTIONS
        );

        $out = [];
        if ($locations !== []) {
            $out['lorebook_locations'] = $locations;
        }
        if ($factions !== []) {
            $out['lorebook_factions'] = $factions;
        }
        return $out;
    }

    /**
     * @param list<array|string> $entities Each item: array with 'name' and optional keys, or string
     * @return list<array>
     */
    private function resolveEntities(array $entities, string $arcLower, int $max): array
    {
        $withName = [];
        foreach ($entities as $e) {
            $name = is_array($e) ? ($e['name'] ?? $e['title'] ?? '') : (string) $e;
            if ($name === '') {
                continue;
            }
            $withName[] = [
                'name' => $name,
                'matched' => $arcLower !== '' && mb_strpos($arcLower, mb_strtolower($name)) !== false,
                'payload' => is_array($e) ? $e : ['name' => $name],
            ];
        }
        usort($withName, fn ($a, $b) => ($b['matched'] ? 1 : 0) <=> ($a['matched'] ? 1 : 0));
        $result = [];
        foreach (array_slice($withName, 0, $max) as $e) {
            $result[] = $e['payload'];
        }
        return $result;
    }
}
