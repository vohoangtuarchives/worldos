<?php

namespace App\Narrative\Exceptions;

use Exception;

class NarrativeViolationException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
