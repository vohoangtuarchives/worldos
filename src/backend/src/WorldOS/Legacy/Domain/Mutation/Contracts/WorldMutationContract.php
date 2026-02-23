<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Mutation\Contracts;

/**
 * Port: describes the permitted delta to apply to Universe (vector delta, optional pressure).
 * Domain-only; vector shape is array to avoid coupling to a specific VO.
 */
interface WorldMutationContract
{
    /** @return array<string, float> vector delta components */
    public function vectorDelta(): array;

    public function pressureDelta(): float;
}
