<?php

namespace App\Console\Commands\Material;

use WorldOS\Legacy\Application\Material\Extraction\ExtractionPipeline;
use Illuminate\Console\Command;

class ExtractMaterialCommand extends Command
{
    protected $signature = 'material:extract
                            {--source= : Source type (wikipedia, dataset, text)}
                            {--url= : Wikipedia URL}
                            {--file= : Dataset or text file path}
                            {--text= : Direct text input}
                            {--title= : Title for text input}';

    protected $description = 'Extract material concepts from various sources';

    public function handle(ExtractionPipeline $pipeline): int
    {
        $source = $this->option('source');

        if (!$source) {
            $this->error('Source type required: --source=wikipedia|dataset|text');
            return 1;
        }

        try {
            $template = match($source) {
                'wikipedia' => $this->extractFromWikipedia($pipeline),
                'dataset' => $this->extractFromDataset($pipeline),
                'text' => $this->extractFromText($pipeline),
                default => throw new \InvalidArgumentException("Unknown source: {$source}"),
            };

            $this->info("✅ Extraction completed!");
            $this->info("Template ID: {$template->id}");
            $this->info("Status: {$template->status}");
            $this->info("Valid: " . ($template->isValid() ? 'Yes' : 'No'));

            if (!$template->isValid()) {
                $this->warn("Validation errors:");
                foreach ($template->getValidationErrors() as $error) {
                    $this->error("  - {$error}");
                }
            }

            if ($template->getValidationWarnings()) {
                $this->warn("Validation warnings:");
                foreach ($template->getValidationWarnings() as $warning) {
                    $this->warn("  - {$warning}");
                }
            }

            $this->info("\nReview at: /admin/material-extraction/{$template->id}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Extraction failed: {$e->getMessage()}");
            return 1;
        }
    }

    private function extractFromWikipedia(ExtractionPipeline $pipeline)
    {
        $url = $this->option('url');
        
        if (!$url) {
            throw new \InvalidArgumentException('Wikipedia URL required: --url=...');
        }

        $this->info("Extracting from Wikipedia: {$url}");
        return $pipeline->extractFromWikipedia($url);
    }

    private function extractFromDataset(ExtractionPipeline $pipeline)
    {
        $file = $this->option('file');
        
        if (!$file) {
            throw new \InvalidArgumentException('Dataset file required: --file=...');
        }

        $this->info("Extracting from dataset: {$file}");
        return $pipeline->extractFromDataset($file);
    }

    private function extractFromText(ExtractionPipeline $pipeline)
    {
        $text = $this->option('text');
        $file = $this->option('file');
        
        if (!$text && !$file) {
            throw new \InvalidArgumentException('Text required: --text=... or --file=...');
        }

        if ($file) {
            $text = file_get_contents($file);
        }

        $title = $this->option('title') ?? 'Text Corpus';

        $this->info("Extracting from text: {$title}");
        return $pipeline->extractFromText($text, $title);
    }
}
