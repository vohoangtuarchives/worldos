<?php

namespace WorldOS\Legacy\Application\Material\Extraction;

use App\Models\MaterialExtractionTemplate;

/**
 * ExtractionPipeline - Orchestrate the full extraction process
 * 
 * Flow: Extract → Normalize → Map → Validate → Save
 */
class ExtractionPipeline
{
    public function __construct(
        private DataExtractor $extractor,
        private ConceptNormalizer $normalizer,
        private ConceptMapper $mapper,
        private MaterialValidator $validator
    ) {}

    /**
     * Process extraction from Wikipedia.
     */
    public function extractFromWikipedia(string $url): MaterialExtractionTemplate
    {
        // Step 1: Extract raw data
        $rawData = $this->extractor->extractFromWikipedia($url);

        return $this->processExtraction($rawData);
    }

    /**
     * Process extraction from dataset.
     */
    public function extractFromDataset(string $path): MaterialExtractionTemplate
    {
        $rawData = $this->extractor->extractFromDataset($path);

        return $this->processExtraction($rawData);
    }

    /**
     * Process extraction from text.
     */
    public function extractFromText(string $text, ?string $title = null): MaterialExtractionTemplate
    {
        $rawData = $this->extractor->extractFromText($text, $title);

        return $this->processExtraction($rawData);
    }

    /**
     * Process extraction pipeline.
     */
    private function processExtraction(array $rawData): MaterialExtractionTemplate
    {
        try {
            // Step 2: Normalize (AI extraction)
            $concepts = $this->normalizer->normalize($rawData);

            // Step 3: Map to materials
            $mappings = $this->mapper->map($concepts);

            // Step 4: Validate (use first new template for now)
            $template = $this->findFirstNewTemplate($mappings);
            
            if (!$template) {
                throw new \RuntimeException('No new material templates generated');
            }

            $validationResult = $this->validator->validate($template);

            // Step 5: Save to database
            return MaterialExtractionTemplate::create([
                'source_type' => $rawData['source_type'],
                'source_url' => $rawData['source_url'],
                'raw_data' => $rawData,
                'extracted_concepts' => $concepts,
                'material_template' => $template,
                'status' => 'pending',
                'validation_result' => $validationResult->toArray(),
            ]);

        } catch (\Exception $e) {
            // Log error and save failed extraction
            return MaterialExtractionTemplate::create([
                'source_type' => $rawData['source_type'] ?? 'unknown',
                'source_url' => $rawData['source_url'] ?? null,
                'raw_data' => $rawData,
                'extracted_concepts' => [],
                'material_template' => [],
                'status' => 'rejected',
                'validation_result' => [
                    'valid' => false,
                    'errors' => [$e->getMessage()],
                    'warnings' => [],
                ],
                'notes' => 'Extraction failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Find first new template from mappings.
     */
    private function findFirstNewTemplate(array $mappings): ?array
    {
        foreach ($mappings as $mapping) {
            if ($mapping['match_type'] === 'new') {
                return $mapping['template'];
            }
        }

        return null;
    }

    /**
     * Batch process multiple sources.
     */
    public function batchProcess(array $sources): array
    {
        $results = [];

        foreach ($sources as $source) {
            try {
                $template = match($source['type']) {
                    'wikipedia' => $this->extractFromWikipedia($source['url']),
                    'dataset' => $this->extractFromDataset($source['path']),
                    'text' => $this->extractFromText($source['text'], $source['title'] ?? null),
                    default => throw new \InvalidArgumentException("Unknown source type: {$source['type']}"),
                };

                $results[] = [
                    'source' => $source,
                    'template_id' => $template->id,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'source' => $source,
                    'error' => $e->getMessage(),
                    'status' => 'failed',
                ];
            }
        }

        return $results;
    }
}
