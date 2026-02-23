<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Evolution\Domain\Fitness;

use PHPUnit\Framework\TestCase;
use WorldOS\Evolution\Domain\Fitness\Service\AdaptiveFitnessController;

final class AdaptiveFitnessControllerTest extends TestCase
{
    public function test_adjusts_weights_based_on_volatility(): void
    {
        $controller = new AdaptiveFitnessController();

        // 1. Ecosystem highly volatile (EVI > 0.7) -> favor stability to save from collapse
        $weightsVolatile = $controller->adjustWeights(0.8);
        $this->assertEqualsWithDelta(0.45, $weightsVolatile->complexity, 0.001); 
        $this->assertEqualsWithDelta(0.3, $weightsVolatile->stability, 0.001);  

        // 2. Ecosystem frozen (EVI < 0.3) -> favor complexity to avoid heat death
        $weightsFrozen = $controller->adjustWeights(0.2);
        $this->assertEqualsWithDelta(0.7, $weightsFrozen->complexity, 0.001);    
        $this->assertEqualsWithDelta(0.15, $weightsFrozen->stability, 0.001);   

        // 3. Normal range
        $weightsNormal = $controller->adjustWeights(0.5);
        $this->assertEqualsWithDelta(0.6, $weightsNormal->complexity, 0.001);
        $this->assertEqualsWithDelta(0.2, $weightsNormal->stability, 0.001);
    }
}
