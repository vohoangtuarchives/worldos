<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Kernel\Domain;

use PHPUnit\Framework\TestCase;
use WorldOS\Kernel\Domain\Compiler\PolicyValidator;
use WorldOS\Kernel\Domain\Policy\KernelPolicy;
use InvalidArgumentException;

class PolicyValidatorTest extends TestCase
{
    private PolicyValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PolicyValidator();
    }

    public function test_it_validates_a_correct_policy()
    {
        $policy = KernelPolicy::fromArray([
            'version' => '1.0',
            'stability' => [
                'chaos_factor' => 0.04,
                'spectral_radius' => 0.95,
            ],
            'evolution' => [
                'mutation_strength' => 0.05,
            ],
            'fork' => [
                'max_active_branches' => 5,
            ],
            'weight' => [
                'formula' => 'clamp(w + anomaly, 0.0, 1.0)',
            ]
        ]);

        $this->expectNotToPerformAssertions();
        $this->validator->validate($policy);
    }

    public function test_it_rejects_excessive_chaos_factor()
    {
        $policy = KernelPolicy::fromArray([
            'version' => '1.0',
            'stability' => [
                'chaos_factor' => 0.06, // Above 0.05 MAX
            ],
            'weight' => ['formula' => 'w']
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Chaos factor exceeds maximum safety limit");
        
        $this->validator->validate($policy);
    }

    public function test_it_rejects_dangerous_eval_tokens_in_weight_formula()
    {
        $policy = KernelPolicy::fromArray([
            'version' => '1.0',
            'weight' => [
                'formula' => 'eval("echo Hacked;")',
            ]
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Weight formula contains forbidden tokens.");
        
        $this->validator->validate($policy);
    }
}
