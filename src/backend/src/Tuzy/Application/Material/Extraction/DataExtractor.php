<?php

namespace Tuzy\Application\Material\Extraction;

use Illuminate\Support\Facades\Http;

/**
 * DataExtractor - Collect raw data from sources
 * 
 * Sources:
 * - Wikipedia (via API)
 * - CSV datasets
 * - Text corpus
 */
class DataExtractor
{
    /**
     * Extract from Wikipedia article.
     * 
     * @param string $url Wikipedia article URL
     * @return array Raw data + metadata
     */
    public function extractFromWikipedia(string $url): array
    {
        // Parse Wikipedia URL to get article title
        $title = $this->parseWikipediaTitle($url);
        
        if (!$title) {
            throw new \InvalidArgumentException("Invalid Wikipedia URL: {$url}");
        }

        // Use Wikipedia API
        $response = Http::get('https://en.wikipedia.org/w/api.php', [
            'action' => 'query',
            'format' => 'json',
            'titles' => $title,
            'prop' => 'extracts|categories|info',
            'explaintext' => true,
            'exintro' => false, // Get full article
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Failed to fetch Wikipedia article: {$title}");
        }

        $data = $response->json();
        $page = array_values($data['query']['pages'])[0] ?? null;

        if (!$page || isset($page['missing'])) {
            throw new \RuntimeException("Wikipedia article not found: {$title}");
        }

        return [
            'source_type' => 'wikipedia',
            'source_url' => $url,
            'title' => $page['title'] ?? $title,
            'content' => $page['extract'] ?? '',
            'metadata' => [
                'categories' => $this->extractCategories($page['categories'] ?? []),
                'page_id' => $page['pageid'] ?? null,
                'last_modified' => $page['touched'] ?? null,
            ],
        ];
    }

    /**
     * Extract from CSV dataset.
     * 
     * @param string $path Path to CSV file
     * @return array Raw data + metadata
     */
    public function extractFromDataset(string $path): array
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Dataset file not found: {$path}");
        }

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $row);
        }

        fclose($handle);

        return [
            'source_type' => 'dataset',
            'source_url' => $path,
            'title' => basename($path, '.csv'),
            'content' => json_encode($rows),
            'metadata' => [
                'row_count' => count($rows),
                'columns' => $headers,
                'file_size' => filesize($path),
            ],
        ];
    }

    /**
     * Extract from text corpus.
     * 
     * @param string $text Raw text content
     * @param string|null $title Optional title
     * @return array Raw data + metadata
     */
    public function extractFromText(string $text, ?string $title = null): array
    {
        return [
            'source_type' => 'text',
            'source_url' => null,
            'title' => $title ?? 'Text Corpus',
            'content' => $text,
            'metadata' => [
                'length' => strlen($text),
                'word_count' => str_word_count($text),
            ],
        ];
    }

    /**
     * Parse Wikipedia article title from URL.
     */
    private function parseWikipediaTitle(string $url): ?string
    {
        // Match: https://en.wikipedia.org/wiki/Article_Title
        if (preg_match('#/wiki/([^?#]+)#', $url, $matches)) {
            return urldecode($matches[1]);
        }

        return null;
    }

    /**
     * Extract category names from Wikipedia API response.
     */
    private function extractCategories(array $categories): array
    {
        return array_map(
            fn($cat) => str_replace('Category:', '', $cat['title'] ?? ''),
            $categories
        );
    }
}
