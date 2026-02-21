<?php

namespace Tests\Unit\Narrative;

use Tuzy\Application\Cosmology\Entities\Universe;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Application\Narrative\Services\LLMChronicler;
use Tuzy\Application\Narrative\Services\NarrativeBridge;
use PHPUnit\Framework\TestCase;

class LLMChroniclerTest extends TestCase
{
    public function test_chronicle_returns_rich_template_when_no_llm()
    {
        $bridge = new NarrativeBridge();
        $chronicler = new LLMChronicler($bridge, null);

        $state = WorldStateVector::create(0.1, 0.95, 0.1, 0.1, 0.1, 0.1); // High entropy → eldritch
        $universe = new Universe($state, [], 'test-chronicler', 0);

        $result = $chronicler->chronicle($universe);

        $this->assertNotEmpty($result);
        $this->assertStringContainsString('chu kỳ', $result);
        $this->assertStringContainsString('entropy', $result);
        $this->assertStringContainsString('Trạng thái mô phỏng', $result);
    }

    public function test_chronicle_reflects_state_eldritch_high_entropy()
    {
        $bridge = new NarrativeBridge();
        $chronicler = new LLMChronicler($bridge, null);

        $state = WorldStateVector::create(0.1, 0.95, 0.1, 0.1, 0.1, 0.1);
        $universe = new Universe($state, [], 'test-eldritch', 42);

        $result = $chronicler->chronicle($universe);

        $this->assertStringContainsString('42', $result);
        $this->assertStringContainsString('0.95', $result);
    }
}
