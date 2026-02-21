<?php

namespace App\Domains\World\Contracts;

use Tuzy\Domain\World\ValueObject\Claim;

interface ClaimExtractorInterface
{
    /**
     * Extract factual claims from narrative text.
     * 
     * @param string $text
     * @return Claim[]
     */
    public function extract(string $text): array;
}
