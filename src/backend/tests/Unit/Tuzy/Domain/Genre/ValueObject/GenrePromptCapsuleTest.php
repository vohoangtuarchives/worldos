<?php

namespace Tests\Unit\Tuzy\Domain\Genre\ValueObject;

use PHPUnit\Framework\TestCase;
use Tuzy\Domain\Genre\ValueObject\GenrePromptCapsule;

final class GenrePromptCapsuleTest extends TestCase
{
    public function test_constructor(): void
    {
        $c = new GenrePromptCapsule('System prompt', ['forbidden'], ['override']);
        $this->assertSame('System prompt', $c->systemPrompt);
        $this->assertSame(['forbidden'], $c->forbiddenConcepts);
        $this->assertSame(['override'], $c->allowedOverrides);
    }

    public function test_defaults(): void
    {
        $c = new GenrePromptCapsule('Only prompt');
        $this->assertSame([], $c->forbiddenConcepts);
        $this->assertSame([], $c->allowedOverrides);
    }
}
