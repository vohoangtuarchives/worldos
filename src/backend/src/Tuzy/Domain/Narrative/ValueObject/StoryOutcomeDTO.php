<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\ValueObject;

use InvalidArgumentException;

/**
 * Final story outcome after optional user override and quantizer.
 * Carries isConfirmed for shadow vs full mutation.
 */
readonly class StoryOutcomeDTO
{
    public const RESULT_WIN = 'win';
    public const RESULT_LOSE = 'lose';
    public const RESULT_PARTIAL = 'partial';

    public function __construct(
        public string $result,
        public float $intensity,
        public string $scope,
        public bool $isConfirmed,
        public ?string $arcId = null,
    ) {
        if (!in_array($result, [self::RESULT_WIN, self::RESULT_LOSE, self::RESULT_PARTIAL], true)) {
            throw new InvalidArgumentException('result must be win, lose, or partial');
        }
        if ($intensity < 0.0 || $intensity > 1.0) {
            throw new InvalidArgumentException('intensity must be in [0, 1]');
        }
    }

    public static function fromDefault(DefaultOutcome $default, bool $isConfirmed = false, ?string $arcId = null): self
    {
        return new self(
            $default->result,
            $default->intensity,
            $default->scope,
            $isConfirmed,
            $arcId,
        );
    }

    public function toArray(): array
    {
        return [
            'result' => $this->result,
            'intensity' => $this->intensity,
            'scope' => $this->scope,
            'is_confirmed' => $this->isConfirmed,
            'arc_id' => $this->arcId,
        ];
    }
}
