<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

/**
 * Represents a persisted OAuth 2.0 access token linked to a client and a user.
 */
final class OAuthAccessToken extends Model
{
    public string $access_token;
    public int $client_internal_id;
    public int $user_id;
    public string $expires_at;
    public string $scope;
    public ?string $created_at = null;
}