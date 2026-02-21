<?php

namespace App\Domains\World\Services;

use App\Models\WorldPrimitive;
use Tuzy\Domain\World\Exception\OntologyViolation;

class PrimitiveGuard
{
    /**
     * Validate that all primitive references exist
     * 
     * @param array $primitiveRefs Format: ['domain' => 'code', ...]
     * @param string $version WFR version
     * @throws OntologyViolation
     */
    public function validate(array $primitiveRefs, string $version = '1.0.0'): void
    {
        foreach ($primitiveRefs as $domain => $code) {
            if (!WorldPrimitive::exists($domain, $code, $version)) {
                throw new OntologyViolation(
                    "Unknown primitive: {$domain}.{$code} in WFR v{$version}"
                );
            }
        }
    }

    /**
     * Validate primitive codes only (domain-agnostic)
     */
    public function validateCodes(array $codes, string $version = '1.0.0'): void
    {
        foreach ($codes as $code) {
            $exists = WorldPrimitive::where('code', $code)
                ->where('version', $version)
                ->where('is_active', true)
                ->exists();

            if (!$exists) {
                throw new OntologyViolation(
                    "Unknown primitive code: {$code} in WFR v{$version}"
                );
            }
        }
    }

    /**
     * Get primitives by domain
     */
    public function getPrimitivesByDomain(string $domain, string $version = '1.0.0'): array
    {
        return WorldPrimitive::where('domain', $domain)
            ->where('version', $version)
            ->where('is_active', true)
            ->pluck('code', 'id')
            ->toArray();
    }
}
