<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\World;
use WorldOS\Legacy\Application\World\AI\ArchitectAdvisor;

class AnalyzeWorldCommand extends Command
{
    protected $signature = 'world:analyze {world_id}';
    protected $description = 'Analyze the world state and generate an AI report';

    public function handle(ArchitectAdvisor $advisor)
    {
        $worldId = $this->argument('world_id');
        $world = World::find($worldId);

        if (! $world) {
            $this->error("World #{$worldId} not found.");
            return 1;
        }

        $this->info("Analyzing World: {$world->name}...");
        
        $advisor->analyze($world);

        $this->info("Analysis complete. Check 'ai_world_reports' table.");
        return 0;
    }
}
