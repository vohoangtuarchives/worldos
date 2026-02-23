<?php

namespace App\Domains\World\Contracts;

use WorldOS\Blueprint\Domain\Legacy\ValueObject\Claim;

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
