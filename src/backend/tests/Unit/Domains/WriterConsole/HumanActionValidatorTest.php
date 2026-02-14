<?php

namespace Tests\Unit\Domains\WriterConsole;

use Tests\TestCase;
use App\Domains\WriterConsole\HumanActionValidator;

class HumanActionValidatorTest extends TestCase
{
    private HumanActionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new HumanActionValidator();
    }

    public function test_allows_seeding()
    {
        $result = $this->validator->validate('seed_archetype', ['key' => 'hero']);
        $this->assertTrue($result->isAllowed());
    }

    public function test_allows_pressure()
    {
        $result = $this->validator->validate('apply_pressure', ['type' => 'scarcity']);
        $this->assertTrue($result->isAllowed());
    }

    public function test_forbids_myth_editing()
    {
        $result = $this->validator->validate('edit_myth', ['id' => '123']);
        $this->assertFalse($result->isAllowed());
        $this->assertStringContainsString('forbidden', $result->getReason());
    }

    public function test_forbids_forcing_outcomes()
    {
        $result = $this->validator->validate('force_outcome', ['winner' => 'faction_a']);
        $this->assertFalse($result->isAllowed());
        $this->assertStringContainsString('forbidden', $result->getReason());
    }
}
