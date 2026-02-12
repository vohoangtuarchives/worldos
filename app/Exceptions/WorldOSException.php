<?php

namespace App\Exceptions;

use Exception;

abstract class WorldOSException extends Exception
{
    /**
     * Context data relevant to the exception.
     */
    protected array $context = [];

    /**
     * Error code for categorization.
     */
    protected string $errorCode;

    public function __construct(string $message, string $errorCode = '', array $context = [], \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->context = $context;
        $this->errorCode = $errorCode;
    }

    /**
     * Get the context data.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get the error code.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Convert exception to array for API responses.
     */
    public function toArray(): array
    {
        return [
            'error' => static::class,
            'message' => $this->getMessage(),
            'code' => $this->getErrorCode(),
            'context' => $this->getContext(),
        ];
    }
}
