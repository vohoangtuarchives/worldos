<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Exception;

use Exception;

final class OntologyViolation extends Exception
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
