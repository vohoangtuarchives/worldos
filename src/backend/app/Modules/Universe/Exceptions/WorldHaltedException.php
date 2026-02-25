<?php

declare(strict_types=1);

namespace App\Modules\Universe\Exceptions;

use RuntimeException;

/**
 * Thrown when a World lifecycle transition is invalid.
 */
class WorldHaltedException extends RuntimeException
{
}
