<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

class OAuthScope extends Model
{
    public string $scope;
    public string $name;
    public string $description;
    public bool $is_default;
}
