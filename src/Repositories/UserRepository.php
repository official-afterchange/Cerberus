<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use Afterchange\Template\Models\User;
use PDO;

/**
 * Handles database queries for user records.
 */
final class UserRepository extends Repository
{
    /**
     * Finds a user by their email address.
     *
     * @param string $email The email address to look up.
     * @return User|null    The hydrated user model, or null if not found.
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->hydrate($data);

        return $user;
    }

    /**
     * Finds a user by their username.
     *
     * @param string $username The username to look up.
     * @return User|null       The hydrated user model, or null if not found.
     */
    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->hydrate($data);

        return $user;
    }

    /**
     * Finds a user by their remember-me token.
     *
     * @param string $token The remember-me token to look up.
     * @return User|null    The hydrated user model, or null if not found.
     */
    public function findByRememberToken(string $token): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE remember_token = :token');
        $stmt->execute(['token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->hydrate($data);

        return $user;
    }

    /**
     * Finds a user by their password reset token.
     *
     * @param string $token The reset token to look up.
     * @return User|null    The hydrated user model, or null if not found.
     */
    public function findByResetToken(string $token): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE reset_token = :token');
        $stmt->execute(['token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->hydrate($data);

        return $user;
    }
}