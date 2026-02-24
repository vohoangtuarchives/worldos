<?php

declare(strict_types=1);

namespace App\WorldOS\World\Exceptions;

use RuntimeException;

/**
 * Thrown when a World lifecycle transition is invalid.
 */
class WorldHaltedException extends RuntimeException
{
}
