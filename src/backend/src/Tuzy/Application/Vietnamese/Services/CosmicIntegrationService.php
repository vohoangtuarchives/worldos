<?php

namespace Tuzy\Application\Vietnamese\Services;

use Tuzy\Domain\Vietnamese\Models\VietnameseHero;
use App\Domains\Cosmic\Services\AttractorService;
use App\Domains\Cosmic\Models\Attractor;

class CosmicIntegrationService
{
    /**
     * Hero archetype → Attractor code mapping
     */
    private const ARCHETYPE_ATTRACTOR_MAP = [
        // Mythological
        'PRIMORDIAL_ANCESTOR' => 'ORIGIN_MYTHOLOGICAL',
        'DRAGON_PROGENITOR' => 'EPIC_DRAGON_CLAN',
        'FAIRY_PROGENITOR' => 'MAGIC_FOREST_REALM',
        'DIVINE_KINGS' => 'ANCIENT_DIVINE_EMPIRE',
        'MOUNTAIN_DEITY' => 'HIGHLAND_MYSTIC',
        'WATER_DEITY' => 'OCEAN_CIVILIZATION',
        'EMERGENCY_SAVIOR' => 'CRISIS_HERO_INTERVENTION',
        'MORTAL_TO_IMMORTAL' => 'ASCENSION_TEMPLE',
        'PROSPERITY_GODDESS' => 'MERCHANT_GUILD_NETWORK',
        'BUILDER_KING' => 'FORTRESS_EMPIRE',
        
        // Resistance
        'RESISTANCE_QUEENS' => 'REBELLION_RIGHTEOUS',
        'WARRIOR_HEROINE' => 'WARRIOR_CLAN',
        'INDEPENDENCE_ATTEMPT' => 'UNDERGROUND_RESISTANCE',
        'REBEL_EMPEROR' => 'PEASANT_REVOLUTION',
        'LOCAL_STRONGMAN' => 'WARLORD_TERRITORIES',
        'INDEPENDENCE_HERO' => 'INDEPENDENCE_VICTORY',
        
        // Dynasty
        'UNIFIER_EMPEROR' => 'UNIFICATION_ERA',
        'WARRIOR_EMPEROR' => 'MILITARY_HEGEMONY',
        'GOLDEN_AGE_FOUNDER' => 'RENAISSANCE_CULTURAL',
        'PATRIOT_GENERAL' => 'PATRIOTIC_MILITARY',
        'LEGENDARY_GENERAL' => 'MILITARY_GENIUS',
        'CONFUCIAN_SCHOLAR' => 'SCHOLAR_BUREAUCRACY',
        'MORAL_REFORMER' => 'ETHICAL_GOVERNANCE',
        'HISTORIAN' => 'CHRONICLE_ARCHIVE',
        
        // Spiritual
        'WARRIOR_MONK_EMPEROR' => 'ENLIGHTENED_WARRIOR',
        'BUDDHIST_SAGE' => 'TEMPLE_MONASTICISM',
        
        // Philosophy & Culture
        'TRI_THUC_KIEN_QUOC' => 'SCHOLAR_PHILOSOPHICAL',
        'SCHOLAR_STRATEGIST' => 'STRATEGIC_ACADEMY',
        'RENAISSANCE_EMPEROR' => 'RENAISSANCE_CULTURAL',
        'ROYAL_HISTORIAN' => 'CHRONICLE_ARCHIVE',
        'CULTURAL_SOUL_ARCHITECT' => 'LITERARY_GOLDEN_AGE',
        'FEMINIST_POET' => 'FEMALE_EMPOWERMENT',
        
        // Territory
        'STRATEGIC_EXILE_FOUNDER' => 'FRONTIER_EXPANSION',
        'SOUTHERN_PIONEER' => 'COLONIAL_EXPANSION',
        'FRONTIER_LORD' => 'BORDER_FORTIFICATION',
        
        // Modern
        'REVOLUTIONARY_LEADER' => 'REVOLUTION_COMMUNIST',
        'REVOLUTIONARY_EMPEROR' => 'PEASANT_REVOLUTION',
        'MODERN_GENERAL' => 'GUERRILLA_WARFARE',
        'MODERN_FEMALE_GENERAL' => 'FEMALE_MILITARY_LEADER',
        'REFORM_VS_REVOLUTION' => 'REFORM_MOVEMENT',
        'RADICAL_JOURNALIST' => 'MEDIA_ACTIVISM',
    ];

    public function __construct(
        private AttractorService $attractorService
    ) {}

    /**
     * Get recommended attractors for a hero based on their archetype and dimensions
     */
    public function getRecommendedAttractors(VietnameseHero $hero, int $limit = 5): array
    {
        $recommendations = [];
        
        // 1. Primary: Map archetype to attractor
        if (isset(self::ARCHETYPE_ATTRACTOR_MAP[$hero->archetype])) {
            $code = self::ARCHETYPE_ATTRACTOR_MAP[$hero->archetype];
            $attractor = Attractor::where('code', $code)->first();
            
            if ($attractor) {
                $recommendations[] = [
                    'attractor' => $attractor,
                    'relevance' => 1.0,
                    'reason' => 'Direct archetype match',
                ];
            }
        }
        
        // 2. Secondary: Match by dominant dimensions
        $topDimensions = $hero->topDimensions;
        
        foreach ($topDimensions as $dimension => $score) {
            $dimensionAttractors = $this->findAttractorsByDimension($dimension, $score);
            
            foreach ($dimensionAttractors as $attractor) {
                $recommendations[] = [
                    'attractor' => $attractor,
                    'relevance' => $score * 0.8,
                    'reason' => "Strong in {$dimension} ({$score})",
                ];
            }
        }
        
        // 3. Deduplicate and sort
        $unique = collect($recommendations)
            ->unique(fn($r) => $r['attractor']->id)
            ->sortByDesc('relevance')
            ->take($limit)
            ->values();
        
        return $unique->toArray();
    }

    /**
     * Find attractors that align with a specific dimension
     */
    private function findAttractorsByDimension(string $dimension, float $score): array
    {
        $dimensionAttractorMap = [
            'military' => ['MILITARY_HEGEMONY', 'WARRIOR_CLAN', 'MILITARY_GENIUS', 'GUERRILLA_WARFARE'],
            'governance' => ['SCHOLAR_BUREAUCRACY', 'ETHICAL_GOVERNANCE', 'UNIFICATION_ERA'],
            'culture' => ['LITERARY_GOLDEN_AGE', 'RENAISSANCE_CULTURAL', 'CHRONICLE_ARCHIVE'],
            'philosophy' => ['SCHOLAR_PHILOSOPHICAL', 'ENLIGHTENED_WARRIOR', 'TEMPLE_MONASTICISM'],
            'rebellion' => ['REBELLION_RIGHTEOUS', 'PEASANT_REVOLUTION', 'UNDERGROUND_RESISTANCE'],
            'spirituality' => ['TEMPLE_MONASTICISM', 'ASCENSION_TEMPLE', 'HIGHLAND_MYSTIC'],
            'territory' => ['FRONTIER_EXPANSION', 'COLONIAL_EXPANSION', 'FORTRESS_EMPIRE'],
            'education' => ['STRATEGIC_ACADEMY', 'SCHOLAR_BUREAUCRACY'],
            'mythic' => ['ORIGIN_MYTHOLOGICAL', 'EPIC_DRAGON_CLAN', 'MAGIC_FOREST_REALM'],
        ];
        
        $codes = $dimensionAttractorMap[$dimension] ?? [];
        
        return Attractor::whereIn('code', $codes)->get()->toArray();
    }

    /**
     * Calculate civilization state boost from active heroes in an era
     */
    public function calculateEraCivilizationBoost(int $era): array
    {
        $heroes = VietnameseHero::where('era', $era)->get();
        
        if ($heroes->isEmpty()) {
            return array_fill_keys(['military', 'governance', 'culture', 'philosophy'], 0.0);
        }
        
        // Average dimension scores = civilization baseline
        $boost = [];
        $dimensions = ['military', 'governance', 'culture', 'philosophy', 'education', 'spirituality'];
        
        foreach ($dimensions as $dim) {
            $avg = $heroes->avg($dim);
            $boost[$dim] = round($avg * 0.1, 3); // Convert to 0-0.1 boost range
        }
        
        return $boost;
    }

    /**
     * Determine if a hero triggers a bifurcation event
     */
    public function checkBifurcationTrigger(VietnameseHero $hero): ?array
    {
        $bifurcationArchetypes = [
            'INDEPENDENCE_HERO',
            'REVOLUTIONARY_LEADER',
            'UNIFIER_EMPEROR',
            'INDEPENDENCE_BIFURCATION',
        ];
        
        if (!in_array($hero->archetype, $bifurcationArchetypes)) {
            return null;
        }
        
        // Check if hero has sufficient impact
        if ($hero->impact_score < 0.7) {
            return null;
        }
        
        // Bifurcation potential
        return [
            'hero_id' => $hero->id,
            'hero_name' => $hero->name,
            'era' => $hero->era,
            'type' => $this->getBifurcationType($hero),
            'probability' => min(1.0, $hero->impact_score),
            'dimensions_involved' => $hero->topDimensions,
        ];
    }

    private function getBifurcationType(VietnameseHero $hero): string
    {
        if ($hero->rebellion > 0.8) {
            return 'INDEPENDENCE_BIFURCATION';
        }
        
        if ($hero->governance > 0.8) {
            return 'UNIFICATION_BIFURCATION';
        }
        
        if ($hero->reform > 0.7) {
            return 'IDEOLOGICAL_BIFURCATION';
        }
        
        return 'GENERAL_BIFURCATION';
    }

    /**
     * Generate archetype resonance data for cosmic state
     */
    public function generateArchetypeResonance(array $heroes): array
    {
        $resonance = [];
        
        foreach ($heroes as $hero) {
            $archetype = $hero->archetype;
            
            if (!isset($resonance[$archetype])) {
                $resonance[$archetype] = [
                    'count' => 0,
                    'avg_impact' => 0.0,
                    'total_impact' => 0.0,
                    'heroes' => [],
                ];
            }
            
            $resonance[$archetype]['count']++;
            $resonance[$archetype]['total_impact'] += $hero->impact_score;
            $resonance[$archetype]['heroes'][] = $hero->name;
        }
        
        // Calculate averages
        foreach ($resonance as $archetype => &$data) {
            $data['avg_impact'] = round($data['total_impact'] / $data['count'], 2);
        }
        
        return $resonance;
    }
}
