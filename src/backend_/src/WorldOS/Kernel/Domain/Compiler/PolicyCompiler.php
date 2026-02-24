<?php

declare(strict_types=1);

namespace WorldOS\Kernel\Domain\Compiler;

use WorldOS\Kernel\Domain\Policy\KernelPolicy;
use WorldOS\Kernel\Domain\Policy\CompiledPolicy;
use InvalidArgumentException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * Parses the raw KernelPolicy JSON/Array DSL and creates a safe, executeable CompiledPolicy.
 * Uses Symfony's ExpressionLanguage to safely compile the weight formula to block dangerous evaluation.
 */
final class PolicyCompiler
{
    private ExpressionLanguage $expressionLanguage;
    private PolicyValidator $validator;

    public function __construct(PolicyValidator $validator)
    {
        $this->validator = $validator;
        // Symfony EL is a safe, sandboxed expression evaluator
        $this->expressionLanguage = new ExpressionLanguage();
        $this->registerSafeMathFunctions();
    }

    /**
     * @throws InvalidArgumentException
     * @throws SyntaxError
     */
    public function compile(KernelPolicy $policy): CompiledPolicy
    {
        // 1. Strict Validation first
        $this->validator->validate($policy);

        $stability = $policy->getStabilityBounds();
        $evolution = $policy->getEvolutionRules();
        $fork = $policy->getForkMechanics();
        $weight = $policy->getWeightFormulas();

        // 2. Safe Expression Compilation
        $formulaString = $weight['formula'] ?? '1.0';
        $allowedVariables = ['w', 'anomaly', 'richness', 'entropy_decay'];
        
        $compiledClosure = $this->compileFormula($formulaString, $allowedVariables);

        // 3. Assemble immutable result
        return CompiledPolicy::create(
            $policy->getVersion(),
            $stability['chaos_factor'] ?? 0.02,
            $stability['spectral_radius'] ?? 0.95,
            $evolution['mutation_strength'] ?? 0.03,
            $fork['max_active_branches'] ?? 10,
            $compiledClosure
        );
    }

    /**
     * Compiles an expression string into a PHP Closure for extremely fast execution in the runtime loop.
     * 
     * @throws SyntaxError
     */
    private function compileFormula(string $formula, array $allowedVariables): \Closure
    {
        // We actually parse and compile to raw PHP code for maximum performance,
        // but it is totally sandboxed by the limited variable set.
        $parsedExpression = $this->expressionLanguage->parse($formula, $allowedVariables);
        
        // This closure maps dynamic variables from context arrays at runtime.
        return function(array $context) use ($parsedExpression): float {
            $result = $this->expressionLanguage->evaluate($parsedExpression, $context);
            
            // Safety bounds for existence weight output [0.0, 1.0]
            if ($result < 0.0) return 0.0;
            if ($result > 1.0) return 1.0;
            
            return (float) $result;
        };
    }

    /**
     * Register safety functions that can be used inside the weight string formulas.
     */
    private function registerSafeMathFunctions(): void
    {
        $this->expressionLanguage->register('min', 
            fn($a, $b) => sprintf('min(%s, %s)', $a, $b), 
            fn($vars, $a, $b) => min($a, $b)
        );

        $this->expressionLanguage->register('max', 
            fn($a, $b) => sprintf('max(%s, %s)', $a, $b), 
            fn($vars, $a, $b) => max($a, $b)
        );
        
        $this->expressionLanguage->register('clamp', 
            fn($val, $min, $max) => sprintf('max(%s, min(%s, %s))', $min, $val, $max), 
            fn($vars, $val, $min, $max) => max($min, min($val, $max))
        );
    }
}
