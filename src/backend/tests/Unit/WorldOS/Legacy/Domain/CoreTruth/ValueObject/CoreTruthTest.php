<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\CoreTruth\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\CoreTruth\ValueObject\Axiom;
use WorldOS\Legacy\Domain\CoreTruth\ValueObject\CoreTruth;

final class CoreTruthTest extends TestCase
{
    public function test_axioms_and_roundtrip(): void
    {
        $a1 = new Axiom('a1', 'First truth');
        $a2 = new Axiom('a2', 'Second truth', false);
        $ct = new CoreTruth('genesis-hash-1', [$a1, $a2]);
        $this->assertSame('genesis-hash-1', $ct->genesisHash);
        $this->assertNotNull($ct->getAxiom('a1'));
        $this->assertTrue($ct->hasAxiom('a2'));
        $this->assertNull($ct->getAxiom('none'));

        $arr = $ct->toArray();
        $restored = CoreTruth::fromArray($arr);
        $this->assertSame($ct->genesisHash, $restored->genesisHash);
        $this->assertCount(2, $restored->axioms);
    }
}
