<?php

namespace Tests\Unit\Tuzy\Domain\Saga\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Saga\ValueObject\CollapseProfile;
use Tuzy\Domain\Saga\ValueObject\SagaEvaluationInput;

final class SagaEvaluationInputTest extends TestCase
{
    public function test_constructor(): void
    {
        $profile = new CollapseProfile(0.5, 'entropy_overload', 'e_vs_o');
        $input = new SagaEvaluationInput($profile, 0.7, 0.6, 0.5, ['k' => 'v']);
        $this->assertSame(0.7, $input->stabilityScore);
        $this->assertSame($profile, $input->collapseProfile);
    }
}