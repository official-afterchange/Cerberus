<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

/**
 * Represents a registered redirect URI associated with an OAuth 2.0 client.
 */
final class OAuthClientRedirect extends Model
{
    public string $client_internal_id;
    public string $redirect_uri;
}