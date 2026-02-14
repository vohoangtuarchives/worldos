<?php

namespace App\Jobs;

use App\Domains\Vietnamese\Models\VietnameseHero;
use App\Domains\Vietnamese\Models\ScoringVersion;
use App\Domains\Vietnamese\Services\HeroScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateHeroScoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $heroId,
        private ?string $versionId = null
    ) {}

    public function handle(HeroScoringService $service): void
    {
        try {
            $hero = VietnameseHero::findOrFail($this->heroId);
            $version = $this->versionId
                ? ScoringVersion::findOrFail($this->versionId)
                : ScoringVersion::active();

            if (!$version) {
                Log::warning("No scoring version available for hero {$this->heroId}");
                return;
            }

            // Calculate all dimensions
            $dimensions = $service->calculateAllDimensions($hero, $version);

            // Calculate overall impact score
            $impactScore = $service->calculateImpactScore($dimensions, $version);

            // Update hero
            $hero->update([
                ...$dimensions,
                'impact_score' => $impactScore,
                'scoring_version_id' => $version->id,
                'last_scored_at' => now(),
            ]);

            Log::info("Recalculated scores for hero {$hero->name}", [
                'impact_score' => $impactScore,
                'top_dimensions' => $hero->fresh()->topDimensions,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to recalculate hero score for {$this->heroId}: {$e->getMessage()}");
            throw $e;
        }
    }
}
