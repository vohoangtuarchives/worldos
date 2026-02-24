
??php

namespace App\Infrastructure\Crawler\Services;

use App\Infrastructure\Crawler\Contracts\MaterialCrawlerInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MaterialCrawlerAggregator
{
    /** @var MaterialCrawlerInterface[] */
    private array $crawlers = [];

    public function registerCrawler(MaterialCrawlerInterface $crawler): void
    {
        $this->crawlers[] = $crawler;
    }

    public function crawl(array $keywords, array $options = []): Collection
    {
        $results = collect();

        foreach ($this->crawlers as $crawler) {
            $payloads = $crawler->crawl($keywords, $options);

            foreach ($payloads as $payload) {
                $normalized = $this->normalizePayload($payload);
                $results->push($normalized);
            }
        }

        return $results
            ->unique(fn ($item) =>
                Str::lower($item['name']) . '|' . ($item['source_url'] ?? 'unknown')
            )
            ->values();
    }

    private function normalizePayload(array $payload): array
    {
        return [
            'name' => trim($payload['name'] ?? ''),
            'category' => Str::slug($payload['category'] ?? 'unknown', '_'),
            'power_tags' => array_values(array_unique($payload['power_tags'] ?? [])),
            'world_affinity' => array_values(array_unique($payload['world_affinity'] ?? [])),
            'origin_locale' => $payload['origin_locale'] ?? 'vi',
            'source_url' => $payload['source_url'] ?? null,
            'rarity' => $payload['rarity'] ?? null,
            'meta' => $payload['meta'] ?? [],
        ];
    }
}