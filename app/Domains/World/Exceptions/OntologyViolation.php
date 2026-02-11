<?php

namespace App\Domains\World\Exceptions;

class OntologyViolation extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Report the exception to logs
     */
    public function report(): bool
    {
        \Log::error('Ontology Violation', [
            'message' => $this->getMessage(),
            'trace' => $this->getTraceAsString(),
        ]);

        return true;
    }
}
