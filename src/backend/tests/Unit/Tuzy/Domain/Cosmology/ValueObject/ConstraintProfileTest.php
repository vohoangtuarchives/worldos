<?php

namespace Tests\Unit\Tuzy\Domain\Cosmology\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Cosmology\ValueObject\ConstraintProfile;

final class ConstraintProfileTest extends TestCase
{
    public function test_from_intent_defaults(): void
    {
        $p = ConstraintProfile::fromIntent([]);
        $this->assertSame('medium', $p->narrativeDensity);
        $this->assertSame('medium', $p->powerGradient);
    }

    public function test_alpha_by_power_gradient(): void
    {
        $steep = ConstraintProfile::fromArray(['power_gradient' => 'steep']);
        $this->assertSame(0.35, $steep->alpha());
        $gentle = ConstraintProfile::fromArray(['power_gradient' => 'gentle']);
        $this->assertSame(0.15, $gentle->alpha());
    }

    public function test_inertia_and_feedback_k(): void
    {
        $p = ConstraintProfile::fromArray(['conflict_intensity' => 'high']);
        $this->assertSame(0.6, $p->inertia());
        $this->assertSame(0.4, $p->feedbackK());
    }
}
