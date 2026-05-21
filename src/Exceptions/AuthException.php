<?php

declare(strict_types=1);

namespace Afterchange\Template\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when an authentication or authorization operation fails with a typed error code.
 */
final class AuthException extends Exception
{
    private string $errorCode;

    /**
     * Creates the exception with a domain-specific error code alongside the standard exception fields.
     *
     * @param string         $errorCode A machine-readable error identifier (e.g., 'INVALID_CREDENTIALS').
     * @param string         $message   Optional human-readable message for logging or debugging.
     * @param int            $code      Optional numeric exception code.
     * @param Throwable|null $previous  Optional previous exception for chaining.
     */
    public function __construct(string $errorCode, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    /**
     * Returns the machine-readable error code identifying the authentication failure.
     *
     * @return string The error code (e.g., 'INVALID_CREDENTIALS', 'MISSING_FIELDS').
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}