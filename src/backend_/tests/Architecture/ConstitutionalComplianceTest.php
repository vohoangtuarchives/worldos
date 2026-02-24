<?php

namespace Tests\Architecture;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class ConstitutionalComplianceTest extends TestCase
{
    /**
     * ADR-1000: Layer Separation
     * Lower layers MUST NOT depend on higher layers.
     * Layers: Kernel > Runtime (Saga) > Historian > Human (Writer)
     */
    public function test_layer_separation_invariants()
    {
        $layers = [
            'CognitiveKernel' => ['Saga', 'Historian', 'WriterConsole'],
            'Saga' => ['Historian', 'WriterConsole'],
            'Historian' => ['WriterConsole'],
        ];

        foreach ($layers as $layer => $forbiddenDependencies) {
            $this->assertLayerDoesNotDependOn($layer, $forbiddenDependencies);
        }
    }

    /**
     * ADR-1004: Historian Non-Interference
     * The Historian Layer MUST NOT influence world state.
     * It should not write to World or Saga tables.
     */
    public function test_historian_non_interference()
    {
        $historianPath = app_path('Domains/Historian');
        if (!File::exists($historianPath)) {
            $this->markTestSkipped('Historian domain not found');
        }

        $files = File::allFiles($historianPath);

        foreach ($files as $file) {
            $content = file_get_contents($file->getRealPath());
            
            // basic heuristic check for write operations
            // This is not perfect but catches obvious violations
            $violatingMethod = false;
            
            if (preg_match('/->save\(/', $content) || 
                preg_match('/->update\(/', $content) || 
                preg_match('/->delete\(/', $content) ||
                preg_match('/::create\(/', $content) ||
                preg_match('/::forceCreate\(/', $content)) {
                
                // Allow exceptions for internal Historian artifacts (DTOs, Analysis results)
                // But block World/Saga modifications
                if (preg_match('/use App\\\\Domains\\\\Saga/', $content) || 
                    preg_match('/use App\\\\Models\\\\World/', $content)) {
                    $violatingMethod = true;
                }
            }

            $this->assertFalse(
                $violatingMethod, 
                "Constitutional Violation (ADR-1004): Historian file {$file->getFilename()} appears to modify state (save/update/create detected with unrelated imports)."
            );
        }
    }

    private function assertLayerDoesNotDependOn(string $layer, array $forbiddenLayers)
    {
        $layerPath = app_path("Domains/{$layer}");
        if (!File::exists($layerPath)) {
            return;
        }

        $files = File::allFiles($layerPath);

        foreach ($files as $file) {
            $content = file_get_contents($file->getRealPath());
            
            foreach ($forbiddenLayers as $forbidden) {
                // Check uses
                $hasForbiddenUse = preg_match(
                    "/use App\\\\Domains\\\\{$forbidden}/", 
                    $content
                );

                $this->assertFalse(
                    $hasForbiddenUse === 1,
                    "Constitutional Violation (ADR-1000): Layer '{$layer}' depends on higher layer '{$forbidden}' in file {$file->getFilename()}."
                );
            }
        }
    }
}
