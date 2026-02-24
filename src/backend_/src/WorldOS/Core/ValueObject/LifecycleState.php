<?php

declare(strict_types=1);

namespace WorldOS\Core\ValueObject;

/**
 * LifecycleState: The evolutionary stages of a civilization.
 */
enum LifecycleState: string
{
    case Emerging = 'emerging';   // New-born civilization, low influence
    case Stable = 'stable';       // Established society, consistent physics
    case Dominant = 'dominant';   // Regional or global influence
    case Ascended = 'ascended';   // Reached high intellectual/cultural density
    case Transformed = 'transformed'; // Post-metamorphosis state
    case Dormant = 'dormant';     // Collapsed or inactive memory
}
