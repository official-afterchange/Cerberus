<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

/**
 * Represents a short-lived OAuth 2.0 authorization code issued during the consent flow.
 */
final class OAuthCode extends Model
{
    public string $code;
    public int $client_internal_id;
    public int $user_id;
    public string $expires_at;
    public string $scope;
    public int $used = 0;
    public ?string $created_at = null;
}