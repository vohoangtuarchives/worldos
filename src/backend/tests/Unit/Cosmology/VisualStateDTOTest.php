<?php

namespace Tests\Unit\Cosmology;

use App\Domains\Cosmology\DataTransferObjects\VisualStateDTO;
use App\Domains\Cosmology\Entities\WorldStateVector;
use PHPUnit\Framework\TestCase;

class VisualStateDTOTest extends TestCase
{
    public function test_maps_high_entropy_to_reddish_color()
    {
        // High Entropy (Red), Low Cohesion (Green), Low Order (Blue)
        $vector = WorldStateVector::create(0.1, 0.9, 0.1, 0.1, 0.2, 0.1);
        $dto = VisualStateDTO::fromVector($vector);
        
        $array = $dto->toArray();
        $this->assertEquals('fire', $array['particle_type']);
        $this->assertGreaterThan(0.5, $array['intensity']);
        // Red component should be high
        // Hex logic: #RRGGBB. Red is first 2 chars after #.
        $hex = $array['color'];
        $r = hexdec(substr($hex, 1, 2));
        $this->assertGreaterThan(200, $r);
    }

    public function test_maps_high_order_to_crystal()
    {
        $vector = WorldStateVector::create(0.9, 0.1, 0.1, 0.1, 0.1, 0.1);
        $dto = VisualStateDTO::fromVector($vector);
        
        $this->assertEquals('crystal', $dto->particleType);
        // Blue component high
        $hex = $dto->colorHex;
        $b = hexdec(substr($hex, 5, 2));
        $this->assertGreaterThan(200, $b);
    }
}
