<?php

namespace Tests\Unit\WorldOS\Saga\Domain\Legacy\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Legacy\ValueObject\CollapseProfile;
use WorldOS\Saga\Domain\Legacy\ValueObject\SagaEvaluationInput;

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
