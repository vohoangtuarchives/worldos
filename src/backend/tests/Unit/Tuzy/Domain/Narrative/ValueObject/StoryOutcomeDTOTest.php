<?php

namespace Tests\Unit\Tuzy\Domain\Narrative\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Narrative\ValueObject\DefaultOutcome;
use Tuzy\Domain\Narrative\ValueObject\StoryOutcomeDTO;

final class StoryOutcomeDTOTest extends TestCase
{
    public function test_constructor_and_to_array(): void
    {
        $dto = new StoryOutcomeDTO(
            StoryOutcomeDTO::RESULT_PARTIAL,
            0.5,
            'narrative',
            true,
            'arc-1'
        );
        $this->assertSame('partial', $dto->result);
        $this->assertSame(0.5, $dto->intensity);
        $this->assertTrue($dto->isConfirmed);
        $this->assertSame('arc-1', $dto->arcId);

        $arr = $dto->toArray();
        $this->assertSame('partial', $arr['result']);
        $this->assertSame(0.5, $arr['intensity']);
        $this->assertTrue($arr['is_confirmed']);
        $this->assertSame('arc-1', $arr['arc_id']);
    }

    public function test_from_default(): void
    {
        $default = new DefaultOutcome(DefaultOutcome::RESULT_WIN, 0.7, DefaultOutcome::SCOPE_NATIONAL);
        $dto = StoryOutcomeDTO::fromDefault($default, true, 'arc-x');
        $this->assertSame('win', $dto->result);
        $this->assertSame(0.7, $dto->intensity);
        $this->assertSame('national', $dto->scope);
        $this->assertTrue($dto->isConfirmed);
        $this->assertSame('arc-x', $dto->arcId);
    }

    public function test_invalid_result_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('result must be win, lose, or partial');
        new StoryOutcomeDTO('invalid', 0.5, 'local', false, null);
    }

    public function test_invalid_intensity_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('intensity must be in [0, 1]');
        new StoryOutcomeDTO(StoryOutcomeDTO::RESULT_WIN, 1.5, 'local', false, null);
    }
}
