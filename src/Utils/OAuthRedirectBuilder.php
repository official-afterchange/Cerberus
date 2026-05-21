<?php

declare(strict_types=1);

namespace Afterchange\Template\Utils;

/**
 * Builds OAuth 2.0 compliant error redirect URIs.
 */
final class OAuthRedirectBuilder
{
    /**
     * Appends OAuth error parameters to a redirect URI, preserving any existing query string.
     *
     * @param string      $redirectUri       The base client redirect URI.
     * @param string      $error             The OAuth 2.0 error code (e.g., 'access_denied').
     * @param string|null $state             The state parameter from the original request, echoed back if provided.
     * @param string|null $errorDescription  An optional human-readable description of the error.
     * @return string                        The fully assembled redirect URI with error parameters.
     */
    public static function build(
        string $redirectUri,
        string $error,
        ?string $state = null,
        ?string $errorDescription = null
    ): string {
        $params = ['error' => $error];

        if ($errorDescription !== null) {
            $params['error_description'] = $errorDescription;
        }

        if ($state !== null) {
            $params['state'] = $state;
        }

        $separator = \str_contains($redirectUri, '?') ? '&' : '?';

        return $redirectUri . $separator . \http_build_query($params);
    }
}