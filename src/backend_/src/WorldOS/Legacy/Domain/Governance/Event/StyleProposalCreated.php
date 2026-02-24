<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Governance\Event;

/**
 * Domain event: a style proposal was created.
 * Carries proposal id only; no Eloquent dependency.
 */
final readonly class StyleProposalCreated
{
    public function __construct(
        public string $proposalId,
        public string $universeStyleId,
    ) {
    }
}
