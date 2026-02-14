<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoryArc extends Model
{
    protected $fillable = [
        'world_state_id',
        'title',
        'arc_type',
        'source_material_seeds',
        'structure',
        'content',
        'estimated_chapters',
        'tension_progression',
    ];

    protected $casts = [
        'source_material_seeds' => 'json',
        'structure' => 'json',
        'content' => 'json',
        'tension_progression' => 'json',
    ];

    public function worldState(): BelongsTo
    {
        return $this->belongsTo(WorldState::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    /**
     * Create story arc from material seeds
     */
    public static function fromMaterialSeeds(WorldState $world, array $seedIds): self
    {
        $seeds = MaterialSeed::whereIn('id', $seedIds)->get();
        
        $arc = new self([
            'world_state_id' => $world->id,
            'title' => self::generateTitle($seeds),
            'arc_type' => self::determineArcType($seeds),
            'source_material_seeds' => $seedIds,
            'structure' => self::generateStructure($seeds),
            'content' => self::generateContent($seeds),
            'estimated_chapters' => self::estimateChapters($seeds),
            'tension_progression' => self::generateTensionProgression($seeds),
        ]);

        return $arc;
    }

    /**
     * Generate HP-style academic arc
     */
    public static function academicSystemArc(WorldState $world): self
    {
        $content = [
            'year_1' => [
                'theme' => 'Discovery and Introduction',
                'conflicts' => ['House rivalries', 'Learning basic magic', 'First friendships'],
                'tension' => 0.3,
                'key_events' => ['Sorting ceremony', 'First classes', 'Quidditch tryouts']
            ],
            'year_3' => [
                'theme' => 'Secrets and Dangers',
                'conflicts' => ['Forbidden knowledge', 'Hidden enemies', 'Family mysteries'],
                'tension' => 0.6,
                'key_events' => ['Forbidden section discovery', 'Secret society formation', 'First confrontation']
            ],
            'year_5' => [
                'theme' => 'Prophecy and Choice',
                'conflicts' => ['Destiny vs free will', 'Power struggles', 'Moral dilemmas'],
                'tension' => 0.8,
                'key_events' => ['Prophecy revelation', 'Mentor sacrifice', 'Major battle']
            ],
            'year_7' => [
                'theme' => 'Confrontation and Transformation',
                'conflicts' => ['Final battle', 'System collapse', 'New world order'],
                'tension' => 1.0,
                'key_events' => ['Final confrontation', 'Victory at cost', 'Rebuilding']
            ]
        ];

        return new self([
            'world_state_id' => $world->id,
            'title' => 'The Scholar\'s Journey: From Student to Savior',
            'arc_type' => 'academic_coming_of_age',
            'source_material_seeds' => [],
            'structure' => ['year_1', 'year_3', 'year_5', 'year_7'],
            'content' => $content,
            'estimated_chapters' => 200,
            'tension_progression' => [0.3, 0.6, 0.8, 1.0],
        ]);
    }

    private static function generateTitle($seeds): string
    {
        $themes = [];
        foreach ($seeds as $seed) {
            if ($seed->seed_type === 'conflict') {
                $themes[] = $seed->content['conflict']['title'];
            }
        }

        if (empty($themes)) {
            return 'Untitled Story Arc';
        }

        return implode(': ', array_slice($themes, 0, 2));
    }

    private static function determineArcType($seeds): string
    {
        $types = [];
        foreach ($seeds as $seed) {
            $types[] = $seed->archetype;
        }

        $typeCounts = array_count_values($types);
        arsort($typeCounts);

        return array_key_first($typeCounts) ?? 'mixed';
    }

    private static function generateStructure($seeds): array
    {
        return [
            'setup' => ['introduction', 'inciting_incident'],
            'rising_action' => ['rising_tension', 'midpoint', 'complications'],
            'climax' => ['final_confrontation', 'resolution'],
            'falling_action' => ['aftermath', 'new_equilibrium']
        ];
    }

    private static function generateContent($seeds): array
    {
        $content = [];
        
        foreach ($seeds as $seed) {
            $content[$seed->id] = [
                'type' => $seed->seed_type,
                'source' => $seed->source_axes,
                'data' => $seed->content,
                'tension' => $seed->tension_level
            ];
        }

        return $content;
    }

    private static function estimateChapters($seeds): int
    {
        $baseChapters = count($seeds) * 10; // 10 chapters per seed
        
        // Add complexity bonus
        $complexityBonus = 0;
        foreach ($seeds as $seed) {
            if ($seed->tension_level > 0.7) {
                $complexityBonus += 5;
            }
        }

        return $baseChapters + $complexityBonus;
    }

    private static function generateTensionProgression($seeds): array
    {
        $tension = [];
        $seeds = $seeds->sortBy('tension_level');

        foreach ($seeds as $seed) {
            $tension[] = $seed->tension_level;
        }

        // Ensure progression ends at 1.0
        if (!empty($tension) && end($tension) < 1.0) {
            $tension[] = 1.0;
        }

        return $tension;
    }

    /**
     * Get chapter outline for specific year/phase
     */
    public function getPhaseOutline(string $phase): array
    {
        return $this->content[$phase] ?? [];
    }

    /**
     * Calculate total tension across arc
     */
    public function getTotalTension(): float
    {
        return array_sum($this->tension_progression) / count($this->tension_progression);
    }

    /**
     * Get most tense phase
     */
    public function getClimaxPhase(): string
    {
        $maxTension = 0;
        $climaxPhase = '';

        foreach ($this->content as $phase => $data) {
            if ($data['tension'] > $maxTension) {
                $maxTension = $data['tension'];
                $climaxPhase = $phase;
            }
        }

        return $climaxPhase;
    }
}
