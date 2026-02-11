<?php

namespace App\Domains\World\AI;

use App\Models\World;
use App\Models\AIWorldReport;

class ArchitectAdvisor
{
    public function __construct(
        protected MythOvergrowthAnalyzer $mythAnalyzer,
        protected ScarClusterAnalyzer $scarAnalyzer
    ) {}

    /**
     * Analyze the world and generate reports.
     * This is READ-ONLY regarding the World State.
     * It writes to AIWorldReport table.
     */
    public function analyze(World $world): void
    {
        $analyzers = [
            $this->mythAnalyzer,
            $this->scarAnalyzer,
        ];

        foreach ($analyzers as $analyzer) {
            $result = $analyzer->analyze($world);

            if ($result) {
                AIWorldReport::create([
                    'world_id' => $world->id,
                    'type' => $result['type'],
                    'content' => $result['content'],
                    'suggestion' => $result['suggestion'],
                ]);
            }
        }
    }
}
