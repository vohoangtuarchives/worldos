<?php

namespace App\Console\Commands;

use App\CivilizationForge\EvolutionService;
use Illuminate\Console\Command;

class CivilizationForge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'civilization:forge 
                            {--intent= : Author intent JSON string}
                            {--anchor= : Structural anchor type}
                            {--preset= : World preset type}
                            {--demo : Generate demo HP-style story}
                            {--interactive : Run in interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate story materials using Civilization Forge engine';

    /**
     * Execute the console command.
     */
    public function handle(EvolutionService $evolutionService)
    {
        $this->info('🔥 Civilization Forge - Material Generation Engine');
        $this->info('============================================');

        $intent = $this->option('intent');
        $anchor = $this->option('anchor');
        $preset = $this->option('preset');

        if ($this->option('demo')) {
            return $this->generateDemo($evolutionService, $preset);
        }

        if (!$intent || !$this->option('interactive')) {
            // Use defaults instead of interactive input
            if (!$intent) {
                $intent = [
                    'narrative_density' => 'medium',
                    'power_gradient' => 'medium', 
                    'resource_density' => 'medium',
                    'perception_complexity' => 'medium',
                    'conflict_intensity' => 'medium',
                    'social_thickness' => 'medium',
                    'mythology_layer' => 'present'
                ];
            }
        } else {
            $intent = $this->collectAuthorIntent();
        }

        if (!$anchor) {
            $anchor = $this->collectStructuralAnchor();
        }

        $this->info("Generating story package with preset: {$preset}, anchor: {$anchor}");
        $this->newLine();

        try {
            $package = $evolutionService->generateStoryPackage($intent, $anchor, $preset);
            $this->displayResults($package);
            
            $this->newLine();
            $this->info('✅ Story package generated successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error generating story package: {$e->getMessage()}");
            return 1;
        }
    }

    private function generateDemo(EvolutionService $evolutionService, ?string $preset = null): int
    {
        $this->info('Generating HP-style Magic School demo...');
        
        // Use preset-specific demo intent if preset is provided
        if ($preset) {
            $intent = $this->getPresetDemoIntent($preset);
        } else {
            $intent = [
                'narrative_density' => 'high',
                'power_gradient' => 'medium',
                'resource_density' => 'scarce',
                'perception_complexity' => 'complex',
                'conflict_intensity' => 'medium',
                'social_thickness' => 'deep',
                'mythology_layer' => 'present'
            ];
        }
        
        $anchor = 'academic_system';

        $anchor = 'academic_system';

        try {
            $package = $evolutionService->generateStoryPackage($intent, $anchor);
            $this->displayResults($package);
            
            $this->newLine();
            $this->info('✅ Demo story package generated successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error generating demo: {$e->getMessage()}");
            return 1;
        }
    }

    private function getPresetDemoIntent(string $preset): array
    {
        $presetIntents = [
            'faith' => [
                'narrative_density' => 'high',
                'power_gradient' => 'gentle',
                'resource_density' => 'scarce',
                'perception_complexity' => 'simple',
                'conflict_intensity' => 'low',
                'social_thickness' => 'deep',
                'mythology_layer' => 'present'
            ],
            'rational' => [
                'narrative_density' => 'medium',
                'power_gradient' => 'steep',
                'resource_density' => 'abundant',
                'perception_complexity' => 'complex',
                'conflict_intensity' => 'medium',
                'social_thickness' => 'medium',
                'mythology_layer' => 'absent'
            ],
            'political' => [
                'narrative_density' => 'high',
                'power_gradient' => 'steep',
                'resource_density' => 'scarce',
                'perception_complexity' => 'complex',
                'conflict_intensity' => 'high',
                'social_thickness' => 'deep',
                'mythology_layer' => 'present'
            ],
            'resource' => [
                'narrative_density' => 'medium',
                'power_gradient' => 'medium',
                'resource_density' => 'abundant',
                'perception_complexity' => 'simple',
                'conflict_intensity' => 'medium',
                'social_thickness' => 'medium',
                'mythology_layer' => 'present'
            ],
            'chaotic' => [
                'narrative_density' => 'low',
                'power_gradient' => 'volatile',
                'resource_density' => 'unpredictable',
                'perception_complexity' => 'chaotic',
                'conflict_intensity' => 'high',
                'social_thickness' => 'shallow',
                'mythology_layer' => 'chaotic'
            ],
            'stable' => [
                'narrative_density' => 'medium',
                'power_gradient' => 'gentle',
                'resource_density' => 'balanced',
                'perception_complexity' => 'simple',
                'conflict_intensity' => 'low',
                'social_thickness' => 'medium',
                'mythology_layer' => 'present'
            ]
        ];

        return $presetIntents[$preset] ?? $presetIntents['stable'];
    }

    private function collectStructuralAnchor(): string
    {
        $this->info('Let\'s configure your structural anchor...');
        $this->newLine();

        $anchor = $this->choice('Structural Anchor:', [
            'academic_system' => 'Academic system - schools, research, knowledge',
            'religious_institution' => 'Religious institution - churches, temples, faith',
            'military_hierarchy' => 'Military hierarchy - armies, command structure',
            'trade_network' => 'Trade network - merchants, commerce routes',
            'magical_academy' => 'Magical academy - magic schools, arcane knowledge',
            'political_court' => 'Political court - nobility, intrigue, governance',
            'underworld_syndicate' => 'Underworld syndicate - criminal networks, black markets',
            'artistic_collective' => 'Artistic collective - guilds, creative communities',
            'research_laboratory' => 'Research laboratory - scientific facilities, experiments'
        ], 'academic_system');

        return $anchor;
    }

    private function displayResults(array $package)
    {
        $this->info('📊 Story Package Results');
        $this->info('========================');

        // Summary
        $summary = $package['package_summary'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Materials', $summary['total_materials']],
                ['Pressure Points', $summary['pressure_points']],
                ['Estimated Chapters', $summary['estimated_chapters']],
                ['Resistance Events', $summary['resistance_events']],
                ['Tension Progression', implode(' → ', $summary['tension_progression'])]
            ]
        );

        // World State
        $world = $package['world_state'];
        $this->newLine();
        $this->info('🌍 World State');
        $this->info('Structural Anchor: ' . $world->structural_anchor);
        $this->info('Resistance Factor: ' . $world->resistance_factor);

        // Story Arc
        $arc = $package['story_arc'];
        $this->newLine();
        $this->info('📖 Story Arc');
        $this->info('Title: ' . $arc->title);
        $this->info('Type: ' . $arc->arc_type);
        $this->info('Estimated Chapters: ' . $arc->estimated_chapters);

        // Materials preview
        $materials = $package['evolution']['materials'];
        if (!empty($materials)) {
            $this->newLine();
            $this->info('🎭 Generated Materials (Preview)');
            
            foreach ($materials as $material) {
                $this->line("• {$material->seed_type}: {$material->archetype} (Tension: {$material->tension_level})");
            }
        }

        // Pressure points
        $pressurePoints = $package['evolution']['pressure_points'];
        if (!empty($pressurePoints)) {
            $this->newLine();
            $this->info('⚡ Pressure Points');
            
            foreach ($pressurePoints as $point) {
                $this->line("• {$point['axes']} at {$point['element']} (Tension: {$point['tension']})");
            }
        }

        // Resistance events
        $resistance = $package['evolution']['resistance'];
        if ($resistance['unpredictable_events']['event']) {
            $this->newLine();
            $this->info('🎲 Unpredictable Event');
            $this->line($resistance['unpredictable_events']['description']);
        }

        if (!empty($resistance['emergent_complexity'])) {
            $this->newLine();
            $this->info('🌪️ Emergent Complexity');
            foreach ($resistance['emergent_complexity'] as $complexity) {
                $this->line("• {$complexity}");
            }
        }
    }
}
