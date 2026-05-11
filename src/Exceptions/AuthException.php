<?php

declare(strict_types=1);

namespace Afterchange\Template\Exceptions;

use Exception;
use Throwable;

class AuthException extends Exception
{
    private string $errorCode;

    public function __construct(string $errorCode, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
