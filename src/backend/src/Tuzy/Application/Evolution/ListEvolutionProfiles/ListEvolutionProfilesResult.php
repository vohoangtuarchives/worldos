<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\ListEvolutionProfiles;

final readonly class ListEvolutionProfilesResult
{
    /** @param list<array{id: string, name: string}> $evolutionProfiles */
    public function __construct(
        public array $evolutionProfiles,
    ) {
    }
}
