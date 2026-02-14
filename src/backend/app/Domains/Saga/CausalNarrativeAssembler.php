<?php

namespace App\Domains\Saga;

use App\Domains\Saga\Services\NarrativeDictionary;
use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use Illuminate\Support\Str;

/**
 * CausalNarrativeAssembler - Deterministic projection of Thermodynamic State into Prose.
 * 
 * Replaces DeepNarrativeAssembler.
 * Rules:
 * 1. No random noise (rand() is forbidden). All variance must come from State or Event hashes.
 * 2. Tone/Mood is derived from Entropy, Strain, and Resilience.
 */
class CausalNarrativeAssembler
{
    private ?\App\Domains\Saga\Author\AuthorPersona $persona = null;
    private Services\ProseThesaurus $thesaurus;
    private Services\LedgerNarrator $ledgerNarrator;
    private ?\App\Domains\Cosmic\Services\SemanticProjector $semanticProjector = null;
    private \App\Domains\Cosmic\Services\PhaseEngine $phaseEngine;

    // Archetype definitions replaced by dynamic phases
    private const PHASE_GOLDEN_AGE = 'golden_age';
    private const PHASE_WAR = 'war';
    private const PHASE_REFORM = 'reform';
    private const PHASE_STAGNATION = 'stagnation';
    private const PHASE_FRAGMENTATION = 'fragmentation';
    private const PHASE_STABILITY = 'stability';

    public function __construct(
        Services\LedgerNarrator $ledgerNarrator,
        ?\App\Domains\Cosmic\Services\SemanticProjector $semanticProjector = null,
        ?\App\Domains\Cosmic\Services\PhaseEngine $phaseEngine = null
    ) {
        $this->thesaurus = new Services\ProseThesaurus();
        $this->ledgerNarrator = $ledgerNarrator;
        $this->semanticProjector = $semanticProjector;
        $this->phaseEngine = $phaseEngine ?? new \App\Domains\Cosmic\Services\PhaseEngine();
    }

    public function setPersona(\App\Domains\Saga\Author\AuthorPersona $persona): void
    {
        $this->persona = $persona;
    }

    /**
     * Assemble narrative from events, strictly governed by thermodynamic state.
     */
    public function assemble(array $events, int $epoch, CosmicState $cosmic, CivilizationState $civ, ?CivilizationState $prevCiv = null): string
    {
        // 1. Determine Phase and Lexicon
        $phase = $this->phaseEngine->determinePhase($civ);
        $lexicon = $this->phaseEngine->getLexicon($phase);
        
        // 2. Calculate Deltas
        $deltas = [];
        if ($prevCiv) {
            $deltas = [
                'prosperity' => $civ->prosperity - $prevCiv->prosperity,
                'stability' => $civ->stability - $prevCiv->stability,
                'entropy' => $civ->internalEntropy - $prevCiv->internalEntropy,
                'military' => $civ->militaryPressure - $prevCiv->militaryPressure,
                'culture' => $civ->culturalEnergy - $prevCiv->culturalEnergy,
            ];
        }

        // 3. Deterministic Seed Base
        $seedBase = "{$cosmic->year}_{$epoch}";

        if (empty($events)) {
            $events = [['type' => 'default', 'severity' => 2, 'narrative_template' => 'default']];
        }

        // Sort events deterministically
        usort($events, fn($a, $b) => 
            ($b['severity'] ?? 0) <=> ($a['severity'] ?? 0) ?: strcmp($a['type'] ?? '', $b['type'] ?? '')
        );

        $chapters = [];
        foreach ($events as $index => $event) {
            $eventSeed = $seedBase . "_{$index}"; 
            $chapters[] = $this->expandEventIntoScene($event, $phase, $lexicon, $deltas, $eventSeed);
        }

        $prose = "Năm {$cosmic->year} (Kỷ nguyên " . Str::upper($phase) . "):\n\n" . implode("\n\n", $chapters);

        // Apply Author Persona Stylization
        if ($this->persona) {
            $prose = $this->persona->stylize($prose);
        }

        return $prose;
    }

    /**
     * Map Thermodynamic State to Narrative Archetype.
     */
    private function determineNarrativeArchetype(CosmicState $cosmic, CivilizationState $civ): string
    {
        // Collapse: Structure failing (High Strain) or System Exhaustion (Low Resilience)
        if ($cosmic->strain > 0.8 || $civ->resilience < 0.2) {
            return self::ARCHETYPE_COLLAPSE;
        }

        // Turbulence: High Energy/Entropy but System is fighting back (High Resilience)
        if ($cosmic->entropy > 0.6 && $civ->resilience > 0.5) {
            return self::ARCHETYPE_TURBULENCE;
        }

        // Stagnation: Low Entropy (Frozen) and Low Energy (No change)
        if ($cosmic->entropy < 0.3 && $cosmic->energy < 0.3) {
            return self::ARCHETYPE_STAGNATION;
        }

        // Golden Age: Reasonable Stability, High Energy, High Resilience
        if ($cosmic->stability > 0.6 && $cosmic->energy > 0.5 && $civ->resilience > 0.7) {
            return self::ARCHETYPE_GOLDEN_AGE;
        }

        return self::ARCHETYPE_NEUTRAL;
    }

    /**
     * Expansion with deterministic selection.
     */
    protected function expandEventIntoScene(array $event, string $phase, array $lexicon, array $deltas, string $seed): string
    {
        $type = $event['type'] ?? 'default';
        $severity = $event['scale'] ?? 5;
        $beats = [];

        // 1. Delta Beat (Signs of Change)
        if (!empty($deltas)) {
            $beats[] = $this->generateDeltaBeat($deltas, $lexicon, $seed . "_delta");
        }

        // 2. Internal Event Rendering
        if ($type === \App\Domains\Cosmic\Services\EventEngine::TYPE_HERO_BIRTH) {
            $beats[] = $this->renderHeroEmergence($event, $lexicon, $seed . "_hero");
        } elseif ($type === \App\Domains\Cosmic\Services\EventEngine::TYPE_BATTLE) {
            $beats[] = $this->renderBattleScene($event, $lexicon, $seed . "_battle");
        } else {
            // Generic Drama beats
            $beats[] = $this->generateIntroBeat($type, $phase, $lexicon, $seed . "_intro");
            $beats[] = $this->generateSensoryBeat($type, $lexicon, $seed . "_sensory");
            $beats[] = $this->pickDeterministicTemplate($type, $severity, $seed . "_outcome");
        }

        return $this->refineAndExpand($beats, $severity, $seed . "_refine");
    }

    private function renderHeroEmergence(array $event, array $lexicon, string $seed): string
    {
        $symbol = $this->pickDeterministic($lexicon['symbols'], $seed);
        $verb = $this->pickDeterministic($lexicon['verbs'], $seed . "_verb");
        $archetype = $event['archetype'] ?? 'anh hùng';
        
        $titles = [
            'chaos_breaker' => 'với thanh gươm của công lý',
            'enlightened_sage' => 'với trí tuệ khai sáng',
            'national_defender' => 'như lá chắn thép của sơn hà',
        ];
        $title = $titles[$archetype] ?? '';

        return "Từ trong những biến động của vận mệnh, một $archetype xuất hiện $title. Giữa điềm báo về $symbol, ý chí của họ đã $verb, gánh vác trọng trách của cả một thời đại.";
    }

    private function renderBattleScene(array $event, array $lexicon, string $seed): string
    {
        $imagery = $this->pickDeterministic($lexicon['imagery'], $seed);
        $verb = $this->pickDeterministic($lexicon['verbs'], $seed . "_verb");
        $status = $event['success'] ? "Trong khói lửa, chính nghĩa đã rạng ngời," : "Bóng tối của thất bại bao trùm,";
        
        return "$status những cuộc giao tranh đẫm máu nhuốm màu $imagery. Tiếng gươm đao $verb, quyết định vận mệnh của những kẻ cầm quyền.";
    }

    private function generateDeltaBeat(array $deltas, array $lexicon, string $seed): string
    {
        $pros = $deltas['prosperity'] ?? 0.0;
        $ent = $deltas['entropy'] ?? 0.0;
        $stab = $deltas['stability'] ?? 0.0;
        
        $imagery = $this->pickDeterministic($lexicon['imagery'], $seed);
        
        if ($stab < -0.05) {
            return "Trật tự bắt đầu lung lay, những dấu hiệu rạn nứt xuất hiện giữa sự $imagery.";
        }
        
        if ($ent > 0.05) {
            return "Hỗn mang âm thầm nảy nở như những dòng thác ngầm, lòng người hoang mang.";
        }
        
        if ($pros > 0.1) {
            return "Sự hào nhoáng của $imagery che lấp những mầm mống tai họa, nhưng dân chúng vẫn được hưởng thái bình.";
        }
        
        return "Vận mệnh luân chuyển âm thầm, lịch sử đang chuẩn bị cho một chương mới.";
    }

    private function generateIntroBeat(string $type, string $phase, array $lexicon, string $seed): string
    {
        // Phase-based prefixes
        $prefixes = match($phase) {
            self::PHASE_GOLDEN_AGE => [
                "Dưới ánh hào quang của hưng thịnh,",
                "Khi nhân gian đạt tới đỉnh cao của sự huy hoàng,",
                "Trong kỷ nguyên vương triều bền vững,",
            ],
            self::PHASE_WAR => [
                "Giữa tiếng trống trận rung chuyển đất trời,",
                "Khi máu và lửa quyện vào những lời thề quân tử,",
                "Trong sự va đập của những tham vọng bá chủ,",
            ],
            self::PHASE_FRAGMENTATION => [
                "Trong sự mục nát của một thời đại hào hùng,",
                "Khi những mảnh vỡ của quyền lực rạn nứt,",
                "Giữa bóng tối của sự phân hóa và hoài nghi,",
            ],
            self::PHASE_REFORM => [
                "Trước ngưỡng cửa của một sự thay đổi vĩ đại,",
                "Khi các học giả và chiến lược gia nỗ lực xoay chuyển vận mệnh,",
            ],
            default => [
                "Trong dòng chảy lặng lẽ của năm tháng,",
                "Giữa những biến động chưa rõ hình hài,",
            ]
        };

        $prefix = $this->pickDeterministic($prefixes, $seed);
        $verb = $this->pickDeterministic($lexicon['verbs'], $seed . "_verb");
        
        return "{$prefix} ý chí của nhân loại {$verb}.";
    }

    private function generateSensoryBeat(string $type, array $lexicon, string $seed): string
    {
        $imagery = $this->pickDeterministic($lexicon['imagery'], $seed);
        $symbol = $this->pickDeterministic($lexicon['symbols'], $seed . "_symbol");
        
        return "Không khí mang theo dư vị của sự {$imagery}, thấp thoáng bóng dáng của {$symbol}.";
    }

    private function generateEnvironmentalBeat(string $type, array $lexicon, string $seed): string
    {
        $imagery = $this->pickDeterministic($lexicon['imagery'], $seed);
        return "Cảnh vật xung quanh nhuốm màu {$imagery}, phản chiếu tâm thế của thời đại.";
    }

    private function generatePerspectiveBeat(array $event, string $seed): string
    {
        $options = [
            "Người dân thì thầm những lời cầu nguyện.",
            "Các học giả vội vã ghi chép lại sự kiện.",
            "Tại các phiên chợ, những câu chuyện lạ bắt đầu lan truyền.",
        ];
        return $this->pickDeterministic($options, $seed);
    }

    private function refineAndExpand(array $beats, int $severity, string $seed): string
    {
        $prose = implode(" ", $beats);
        
        // Style selection logic
        $style = 'literary';
        if ($this->persona) {
            $style = match($this->persona->tone) {
                'grand_oriental' => 'han_viet',
                'cynical_dark' => 'literary',
                default => 'literary'
            };
        }

        // Set deterministic seed for Thesaurus
        $this->thesaurus->setSeed($seed);

        // Thesaurus enrichment
        $prose = $this->thesaurus->enrich($prose, $style, 70);

        return $prose;
    }

    private function stylizeEpic(string $text): string
    {
        if ($this->persona) {
            return $this->persona->stylize($text);
        }
        return $text;
    }

    // --- Deterministic Helpers ---

    private function pickDeterministic(array $options, string $seed): mixed
    {
        if (empty($options)) return null;
        $hash = crc32($seed);
        $keys = array_keys($options);
        $index = $hash % count($keys);
        return $options[$keys[$index]];
    }

    private function pickDeterministicTemplate(string $type, int $severityScore, string $seed): string
    {
        // Get raw templates from dictionary
        $templates = NarrativeDictionary::getTemplates();
        $categoryTemplates = $templates[$type] ?? $templates['default'];

        // Map severity score (0-100) to levels 1, 2, 3 (Logic mirrored from NarrativeDictionary)
        $level = match(true) {
            $severityScore >= 8 => 3,
            $severityScore >= 4 => 2,
            default => 1,
        };

        // Fallback to lower levels if current level empty
        $options = $categoryTemplates[$level] ?? $categoryTemplates[2] ?? $categoryTemplates[1];

        return $this->pickDeterministic($options, $seed);
    }

    private function hashToFloat(string $seed): float
    {
        // Returns 0.0 to 1.0
        return (crc32($seed) & 0xFFFFFFFF) / 4294967295;
    }
}
