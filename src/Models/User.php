<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

class User extends Model
{
    public string $username;
    public string $email;
    public string $password;
    public ?string $remember_token = null;
    public ?string $reset_token = null;
    public ?string $reset_expires_at = null;
    public ?string $created_at = null;
    public \DateTime $updated_at;
}
