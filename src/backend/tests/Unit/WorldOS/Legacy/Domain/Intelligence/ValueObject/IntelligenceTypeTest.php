<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\Intelligence\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceType;

final class IntelligenceTypeTest extends TestCase
{
    public function test_cases_and_priority(): void
    {
        $this->assertSame(1, IntelligenceType::THREAT_ASSESSMENT->getPriority());
        $this->assertSame('threat_assessment', IntelligenceType::THREAT_ASSESSMENT->value);
    }

    public function test_is_actionable(): void
    {
        $this->assertTrue(IntelligenceType::THREAT_ASSESSMENT->isActionable());
        $this->assertTrue(IntelligenceType::EVENT_ANALYSIS->isActionable());
        $this->assertFalse(IntelligenceType::PATTERN_DETECTION->isActionable());
    }

    public function test_reliability_weight(): void
    {
        $this->assertEqualsWithDelta(0.9, IntelligenceType::ENVIRONMENTAL_SCAN->getReliabilityWeight(), 1e-9);
    }
}
