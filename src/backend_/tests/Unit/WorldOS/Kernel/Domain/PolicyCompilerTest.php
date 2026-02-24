<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Kernel\Domain;

use PHPUnit\Framework\TestCase;
use WorldOS\Kernel\Domain\Compiler\PolicyCompiler;
use WorldOS\Kernel\Domain\Compiler\PolicyValidator;
use WorldOS\Kernel\Domain\Policy\KernelPolicy;

class PolicyCompilerTest extends TestCase
{
    private PolicyCompiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new PolicyCompiler(new PolicyValidator());
    }

    public function test_it_compiles_safe_math_functions_correctly()
    {
        $policy = KernelPolicy::fromArray([
            'version' => '1.0',
            'weight' => [
                'formula' => 'clamp(w + (anomaly * 0.5), 0.0, 1.0)'
            ]
        ]);

        $compiled = $this->compiler->compile($policy);
        
        // Base weight = 0.5, Anomaly = 0.5 => 0.5 + 0.25 = 0.75
        $result = $compiled->evaluateWeight([
            'w' => 0.5,
            'anomaly' => 0.5,
            'richness' => 0.0,
            'entropy_decay' => 0.0,
        ]);

        $this->assertEquals(0.75, $result);
    }

    public function test_it_clamps_excessive_weight_results()
    {
        $policy = KernelPolicy::fromArray([
            'version' => '1.0',
            'weight' => [
                // Without clamp explicitly in formula, compiler still safety-bounds 0.0 - 1.0! 
                'formula' => 'w + anomaly' 
            ]
        ]);

        $compiled = $this->compiler->compile($policy);
        
        // Base = 0.5, Anomaly = 1.0 => 1.5 => Should clamp to 1.0
        $result = $compiled->evaluateWeight([
            'w' => 0.5,
            'anomaly' => 1.0,
            'richness' => 0.0,
            'entropy_decay' => 0.0,
        ]);

        $this->assertEquals(1.0, $result);
        
        // Negative test
        $resultNegative = $compiled->evaluateWeight([
            'w' => 0.0,
            'anomaly' => -1.0,
            'richness' => 0.0,
            'entropy_decay' => 0.0,
        ]);

        $this->assertEquals(0.0, $resultNegative);
    }
}
