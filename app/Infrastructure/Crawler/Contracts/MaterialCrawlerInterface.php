<?php

namespace App\Infrastructure\Crawler\Contracts;

use Illuminate\Support\Collection;

interface MaterialCrawlerInterface
{
    /**
     * Crawl external sources and return raw material payloads.
     *
     * Each payload should at minimum contain:
     * - `name`: string
     * - `category`: string (e.g. mineral, herb, artifact)
     * - `power_tags`: string[] (keywords relating to power systems)
     * - `world_affinity`: string[] (genre/world preset keys)
     * - `source_url`: string
     * - `origin_locale`: string (vi/en/...)
     * - `meta`: mixed[] (free-form extra data)
     */
    public function crawl(array $keywords, array $options = []): Collection;
}