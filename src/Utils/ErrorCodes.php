<?php

declare(strict_types=1);

namespace Afterchange\Template\Utils;

class ErrorCodes
{
    public const MISSING_FIELDS = 'MISSING_FIELDS';
    public const EMAIL_ALREADY_EXISTS = 'EMAIL_ALREADY_EXISTS';
    public const USERNAME_ALREADY_TAKEN = 'USERNAME_ALREADY_TAKEN';
    public const REGISTRATION_FAILED = 'REGISTRATION_FAILED';
    public const INVALID_EMAIL = 'INVALID_EMAIL';
    public const WEAK_PASSWORD = 'WEAK_PASSWORD';
    public const LOGIN_FAILED = 'LOGIN_FAILED';
    public const TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
}
