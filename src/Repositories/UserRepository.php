<?php

declare(strict_types=1);

namespace Afterchange\Template\Repositories;

use Afterchange\Template\Models\User;
use PDO;

class UserRepository extends Repository
{
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->hydrate($data);

        return $user;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->hydrate($data);

        return $user;
    }

    public function findByRememberToken(string $token): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE remember_token = :token");
        $stmt->execute(['token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $user = new User();
        $user->hydrate($data);

        return $user;
    }

    public function findByResetToken(string $token): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE reset_token = :token");
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
