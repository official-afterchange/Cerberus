<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

/**
 * Represents an application user with authentication and password reset state.
 */
final class User extends Model
{
    public string $username;
    public string $email;
    public string $password;
    public ?string $remember_token = null;
    public ?string $reset_token = null;
    public ?string $reset_expires_at = null;
    public \DateTime $created_at;
    public \DateTime $updated_at;
}