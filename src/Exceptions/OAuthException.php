<?php

declare(strict_types=1);

namespace Afterchange\Template\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when an OAuth 2.0 flow fails, carrying the error code, redirect URI, and state for a compliant error redirect.
 */
final class OAuthException extends Exception
{
    private string $oauthError;
    private ?string $redirectUri;
    private ?string $state;

    /**
     * Creates the exception with all fields required to build a compliant OAuth error redirect.
     *
     * @param string         $oauthError  The OAuth 2.0 error code (e.g., 'invalid_scope', 'access_denied').
     * @param string         $messageKey  A translation key used as the exception message for internal logging.
     * @param string|null    $redirectUri The client redirect URI to send the error response to, if available.
     * @param string|null    $state       The state parameter from the original request, echoed back to the client.
     * @param int            $httpCode    The HTTP status code associated with this error (default: 400).
     * @param Throwable|null $previous    Optional previous exception for chaining.
     */
    public function __construct(
        string $oauthError,
        string $messageKey,
        ?string $redirectUri = null,
        ?string $state = null,
        int $httpCode = 400,
        ?Throwable $previous = null
    ) {
        parent::__construct($messageKey, $httpCode, $previous);
        $this->oauthError = $oauthError;
        $this->redirectUri = $redirectUri;
        $this->state = $state;
    }

    /**
     * Returns the OAuth 2.0 error code to include in the redirect response.
     *
     * @return string The error code (e.g., 'invalid_scope', 'unsupported_response_type').
     */
    public function getOAuthError(): string
    {
        return $this->oauthError;
    }

    /**
     * Returns the client redirect URI to send the error response to.
     *
     * @return string|null The redirect URI, or null if unavailable.
     */
    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    /**
     * Returns the state parameter from the original OAuth request.
     *
     * @return string|null The state value, or null if not provided.
     */
    public function getState(): ?string
    {
        return $this->state;
    }
}