<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Narrative\ValueObject;

use InvalidArgumentException;

/**
 * Default outcome from RuleEngine (before user override).
 * result: win | lose | partial
 */
readonly class DefaultOutcome
{
    public const RESULT_WIN = 'win';
    public const RESULT_LOSE = 'lose';
    public const RESULT_PARTIAL = 'partial';

    public const SCOPE_LOCAL = 'local';
    public const SCOPE_NATIONAL = 'national';
    public const SCOPE_GLOBAL = 'global';

    public function __construct(
        public string $result,
        public float $intensity,
        public string $scope = self::SCOPE_LOCAL,
    ) {
        if (!in_array($result, [self::RESULT_WIN, self::RESULT_LOSE, self::RESULT_PARTIAL], true)) {
            throw new InvalidArgumentException('result must be win, lose, or partial');
        }
        if ($intensity < 0.0 || $intensity > 1.0) {
            throw new InvalidArgumentException('intensity must be in [0, 1]');
        }
    }
}
