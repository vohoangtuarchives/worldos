<?php

namespace Tuzy\Application\World\Services;

class SnapshotMigrator
{
    public function migrate(array $snapshot, int $targetVersion): array
    {
        // Simple logic for now: just return as is or handle basic version bumps.
        // In real impl, this would switch on version and apply patches.
        
        $currentVersion = $snapshot['meta']['version'] ?? 1;
        
        if ($currentVersion < $targetVersion) {
            $snapshot['meta']['version'] = $targetVersion;
        }

        return $snapshot;
    }
}
