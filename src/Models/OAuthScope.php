<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

/**
 * Represents a permission scope that can be requested by an OAuth 2.0 client.
 */
final class OAuthScope extends Model
{
    public string $scope;
    public string $name;
    public string $description;
    public bool $is_default;
}