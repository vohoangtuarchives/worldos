<?php

namespace App\Console\Commands;

use App\Infrastructure\Crawler\Services\MaterialCrawlerAggregator;
use WorldOS\Legacy\Application\Material\Services\MaterialIngestionService;
use App\Models\World;
use Illuminate\Console\Command;

class CrawlMaterialsCommand extends Command
{
    protected $signature = 'world:crawl-materials {--world=} {--keywords=}';

    protected $description = 'Crawl external knowledge bases to ingest materials/resources into worlds';

    public function handle(
        MaterialCrawlerAggregator $aggregator,
        MaterialIngestionService $ingestion
    ): int {
        $keywords = array_filter(array_map('trim', explode(',', $this->option('keywords') ?? 'linh thạch, rare herb')));

        $worldId = $this->option('world');
        $world = $worldId ? World::find($worldId) : null;

        if ($worldId && !$world) {
            $this->error("World {$worldId} not found");
            return self::FAILURE;
        }

        $this->info('Crawling materials with keywords: '.implode(', ', $keywords));

        $payloads = $aggregator->crawl($keywords, [
            'locale' => 'vi',
            'limit' => 50,
        ]);

        $this->info('Ingesting '. $payloads->count() .' materials');

        $ingestion->ingest($payloads, $world);

        $this->info('Material ingestion completed');

        return self::SUCCESS;
    }
}
