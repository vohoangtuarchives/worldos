<?php

namespace App\Console\Commands;

use WorldOS\Legacy\Domain\Vietnamese\Models\VietnameseHero;
use WorldOS\Legacy\Domain\Vietnamese\Models\ScoringVersion;
use App\Jobs\RecalculateHeroScoreJob;
use Illuminate\Console\Command;

class RecalculateHeroScoresCommand extends Command
{
    protected $signature = 'heroes:recalculate 
                            {--version= : Scoring version to use (default: active)}
                            {--hero= : Specific hero ID to recalculate}
                            {--async : Run asynchronously via queue}';

    protected $description = 'Recalculate dimension scores for Vietnamese heroes';

    public function handle(): void
    {
        $versionId = $this->getVersionId();
        $heroId = $this->option('hero');
        $async = $this->option('async');

        if ($heroId) {
            $this->recalculateSingle($heroId, $versionId, $async);
        } else {
            $this->recalculateAll($versionId, $async);
        }
    }

    private function recalculateSingle(string $heroId, ?string $versionId, bool $async): void
    {
        $hero = VietnameseHero::findOrFail($heroId);

        $this->info("Recalculating: {$hero->name}");

        if ($async) {
            dispatch(new RecalculateHeroScoreJob($heroId, $versionId));
            $this->info('Job dispatched to queue');
        } else {
            (new RecalculateHeroScoreJob($heroId, $versionId))->handle(app(HeroScoringService::class));
            $hero->refresh();
            $this->info("Impact score: {$hero->impact_score}");
            $this->table(
                ['Dimension', 'Score'],
                collect($hero->topDimensions)->map(fn($v, $k) => [$k, $v])
            );
        }
    }

    private function recalculateAll(?string $versionId, bool $async): void
    {
        $count = VietnameseHero::count();
        $this->info("Recalculating {$count} heroes...");

        $bar = $this->output->createProgressBar($count);

        VietnameseHero::chunk(100, function ($heroes) use ($versionId, $async, $bar) {
            foreach ($heroes as $hero) {
                if ($async) {
                    dispatch(new RecalculateHeroScoreJob($hero->id, $versionId));
                } else {
                    $hero->recalculateScores($versionId);
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info($async ? 'Jobs dispatched!' : 'Recalculation complete!');
    }

    private function getVersionId(): ?string
    {
        $version = $this->option('version');

        if ($version) {
            return ScoringVersion::where('version', $version)->firstOrFail()->id;
        }

        return null; // Will use active version
    }
}
