<?php

declare(strict_types=1);

namespace Afterchange\Template\Services;

use Afterchange\Template\Models\User;
use Afterchange\Template\Repositories\UserRepository;
use Afterchange\Template\Utils\ErrorCodes;
use Afterchange\Template\Exceptions\AuthException;

class AuthService extends Service
{
    /** @var UserRepository */
    protected \Afterchange\Template\Repositories\Repository $repository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct($userRepository);
    }

    public function currentUser(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->repository->find($_SESSION['user_id']);
    }

    public function login(array $data): User
    {
        $identifier = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            throw new AuthException(ErrorCodes::MISSING_FIELDS);
        }

        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $this->repository->findByEmail($identifier)
            : $this->repository->findByUsername($identifier);

        if (!$user || !\password_verify($password, $user->password)) {
            throw new AuthException(ErrorCodes::LOGIN_FAILED);
        }

        return $user;
    }

    public function register(array $data): User
    {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new AuthException(ErrorCodes::INVALID_EMAIL);
        }

        if ($this->repository->findByEmail($data['email'])) {
            throw new AuthException(ErrorCodes::EMAIL_ALREADY_EXISTS);
        }

        if ($this->repository->findByUsername($data['username'])) {
            throw new AuthException(ErrorCodes::USERNAME_ALREADY_TAKEN);
        }

        if (strlen($data['password']) < 8) {
            throw new AuthException(ErrorCodes::WEAK_PASSWORD);
        }

        $user = new User();
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = \password_hash($data['password'], PASSWORD_BCRYPT);

        if (!$this->repository->save($user)) {
            throw new AuthException(ErrorCodes::REGISTRATION_FAILED);
        }

        return $user;
    }

    public function updateUser(int $id, array $data): User
    {
        /** @var User */
        $user = $this->repository->find($id);
        if (!$user) {
            throw new \Exception("User not found.");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new AuthException(ErrorCodes::INVALID_EMAIL);
        }

        $existing = $this->repository->findByEmail($data['email']);
        if ($existing && $existing->id !== $id) {
            throw new AuthException(ErrorCodes::EMAIL_ALREADY_EXISTS);
        }

        $user->username = $data['username'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = \password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (!$this->repository->save($user)) {
            throw new \Exception("Failed to update user.");
        }

        return $user;
    }

    public function createRememberToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $user->remember_token = $token;
        $this->repository->save($user);

        return $token;
    }

    public function clearRememberToken(User $user): void
    {
        $user->remember_token = null;
        $this->repository->save($user);
    }

    public function loginFromToken(string $token): ?User
    {
        if (method_exists($this->repository, 'findByRememberToken')) {
            return $this->repository->findByRememberToken($token);
        }
        return null;
    }

    public function generateResetToken(string $email): ?string
    {
        $user = $this->repository->findByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $user->reset_token = $token;
        $user->reset_expires_at = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');

        $this->repository->save($user);

        return $token;
    }

    public function validateResetToken(string $token): ?User
    {
        $user = $this->repository->findByResetToken($token);

        if (!$user) {
            return null;
        }

        if (new \DateTime() > new \DateTime($user->reset_expires_at)) {
            return null;
        }

        return $user;
    }

    public function resetPassword(User $user, string $newPassword): bool
    {
        if (strlen($newPassword) < 8) {
            throw new AuthException(ErrorCodes::WEAK_PASSWORD);
        }

        $user->password = \password_hash($newPassword, PASSWORD_BCRYPT);
        $user->reset_token = null;
        $user->reset_expires_at = null;

        return $this->repository->save($user);
    }
}
