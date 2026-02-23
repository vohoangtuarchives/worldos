<?php

namespace WorldOS\Legacy\Application\Narrative\Services;

use WorldOS\Saga\Domain\Narrative\Models\MaterialSeed;
use WorldOS\Saga\Domain\Narrative\Models\StoryPremise;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MaterialGenerator
{
    /**
     * Generate a new story premise based on optional filters.
     *
     * @param array $filters ['power_system' => 'ID', 'tags' => ['cyberpunk']]
     * @return StoryPremise
     */
    public function generatePremise(array $filters = []): StoryPremise
    {
        // 1. Select distinct components
        $powerSystem = $this->selectSeed('power_system', $filters);
        
        // Filter subsequent seeds based on compatibility with the PowerSystem
        $socialStructure = $this->selectSeed('social_structure', $filters, $powerSystem);
        $twist = $this->selectSeed('twist', $filters, $powerSystem); // Twist might not need strict compatibility, but good to check
        $hiddenTruth = $this->selectSeed('hidden_truth', $filters, $powerSystem);

        // 2. Generate a title and summary
        $title = $this->generateTitle($powerSystem, $socialStructure, $twist);
        $summary = $this->generateSummary($powerSystem, $socialStructure, $twist, $hiddenTruth);
        
        // 3. Generate Power Escalation (Placeholder for now, separate service later)
        $escalation = $this->generateEscalation($powerSystem);

        // 4. Create and return the Premise
        return StoryPremise::create([
            'id' => Str::uuid(),
            'title' => $title,
            'summary' => $summary,
            'components' => [
                'power_system' => $powerSystem->id,
                'social_structure' => $socialStructure->id,
                'twist' => $twist->id,
                'hidden_truth' => $hiddenTruth->id,
            ],
            'power_escalation' => $escalation,
        ]);
    }

    protected function selectSeed(string $type, array $filters, ?MaterialSeed $context = null): MaterialSeed
    {
        $query = MaterialSeed::where('type', $type);

        if (isset($filters[$type])) {
            return MaterialSeed::findOrFail($filters[$type]);
        }

        if ($context && !empty($context->compatibility_tags)) {
            // Basic compatibility: prioritize seeds that share at least one tag, 
            // OR have 'any' in their compatibility tags.
            // This is a naive implementation; for production, we might want weighted randomness.
            // For now, let's just pick random.
             $query->inRandomOrder();
        } else {
             $query->inRandomOrder();
        }

        return $query->firstOrFail();
    }

    protected function generateTitle(MaterialSeed $power, MaterialSeed $social, MaterialSeed $twist): string
    {
        $formats = [
            "The {$twist->name} in the Age of {$power->name}",
            "{$social->name}: {$twist->name}",
            "Rise of the {$power->name} User",
            "{$twist->name} amidst {$social->name}",
        ];

        return $formats[array_rand($formats)];
    }

    protected function generateSummary(MaterialSeed $power, MaterialSeed $social, MaterialSeed $twist, MaterialSeed $hidden): string
    {
        return "In a world dominated by **{$social->name}** where **{$power->name}** determines one's fate, the protagonist encounters a unique twist: **{$twist->name}**. \n\n" .
               "As they navigate this dangerous society, they slowly uncover a terrifying truth: **{$hidden->name}**.";
    }

    protected function generateEscalation(MaterialSeed $power): array
    {
        // Simple distinct logic based on name/tags
        $name = strtolower($power->name);

        if (str_contains($name, 'cultivation')) {
            return [
                'Tier 1' => 'Qi Condensation (Luyện Khí)',
                'Tier 2' => 'Foundation Establishment (Trúc Cơ)',
                'Tier 3' => 'Golden Core (Kim Đan)',
                'Tier 4' => 'Nascent Soul (Nguyên Anh)',
                'Tier 5' => 'Dao Seeking (Vấn Đạo)',
            ];
        }

        if (str_contains($name, 'system')) {
            return [
                'Tier 1' => 'Level 1-10 (Noob)',
                'Tier 2' => 'Level 11-30 (Advanced)',
                'Tier 3' => 'Level 31-60 (Elite)',
                'Tier 4' => 'Level 61-99 (Master)',
                'Tier 5' => 'Level 100+ (Transcendent)',
            ];
        }

        if (str_contains($name, 'magic')) {
             return [
                'Tier 1' => 'Apprentice',
                'Tier 2' => 'Adept',
                'Tier 3' => 'Magus',
                'Tier 4' => 'Archmage',
                'Tier 5' => 'Grand Wizard',
            ];
        }

        return ['Tier 1' => 'Novice', 'Tier 2' => 'Expert', 'Tier 3' => 'Master', 'Tier 4' => 'Legend'];
    }
}
