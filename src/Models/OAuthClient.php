<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

use DateTime;

/**
 * Represents a registered OAuth 2.0 client application.
 */
final class OAuthClient extends Model
{
    public string $client_id;
    public string $client_secret;
    public string $name;
    public string $description;
    public DateTime $created_at;
}