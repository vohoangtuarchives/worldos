<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domains\Material\Contracts\MaterialRepositoryInterface;
use App\Domains\Material\Extraction\MaterialValidator;
use Illuminate\Support\Facades\File;

class MaterialIngestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'material:ingest {file : Path to JSON file containing material candidates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingest extracted material candidates into the system.';

    /**
     * Execute the console command.
     */
    public function handle(
        MaterialRepositoryInterface $repository,
        MaterialValidator $validator
    ) {
        $path = $this->argument('file');

        if (!File::exists($path)) {
            $this->error("File not found: $path");
            return 1;
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON file.");
            return 1;
        }

        $candidates = $data['candidate_materials'] ?? [];

        if (empty($candidates)) {
            $this->warn("No candidates found in 'candidate_materials' key.");
            return 0;
        }

        $count = 0;
        $errors = 0;

        foreach ($candidates as $candidate) {
            $validation = $validator->validate($candidate);

            if (!$validation['valid']) {
                $this->error("Skipping {$candidate['code']}: " . implode(', ', $validation['errors']));
                $errors++;
                continue;
            }

            if ($repository->findByCode($candidate['code'])) {
                $this->warn("Skipping {$candidate['code']}: Already exists.");
                continue;
            }

            // Create Material
            try {
                $repository->create($candidate);
                $this->info("Ingested: {$candidate['code']}");
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to save {$candidate['code']}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("Ingestion complete. Imported: $count, Errors/Skipped: $errors");
        return 0;
    }
}
