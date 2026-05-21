<?php

declare(strict_types=1);

namespace Afterchange\Template\Services;

use Afterchange\Template\Exceptions\AuthException;
use Afterchange\Template\Models\User;
use Afterchange\Template\Repositories\UserRepository;
use Afterchange\Template\Utils\ErrorCodes;

/**
 * Handles user authentication, registration, profile updates, and password reset workflows.
 */
final class AuthService extends Service
{
    /**
     * @var UserRepository
     */
    protected \Afterchange\Template\Repositories\Repository $repository;

    /**
     * Initializes the service with the user repository.
     *
     * @param UserRepository $userRepository The repository used for all user database operations.
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct($userRepository);
    }

    /**
     * Returns the currently authenticated user from the session.
     *
     * @return User|null The hydrated user model, or null if no session is active.
     */
    public function currentUser(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->repository->find($_SESSION['user_id']);
    }

    /**
     * Authenticates a user by username or email and verifies their password.
     *
     * @param array $data Expects 'username' (or email) and 'password' keys.
     * @return User       The authenticated user model.
     * @throws AuthException If credentials are missing or invalid.
     */
    public function login(array $data): User
    {
        $identifier = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            throw new AuthException(ErrorCodes::MISSING_FIELDS);
        }

        $user = \filter_var($identifier, \FILTER_VALIDATE_EMAIL)
            ? $this->repository->findByEmail($identifier)
            : $this->repository->findByUsername($identifier);

        if (!$user || !\password_verify($password, $user->password)) {
            throw new AuthException(ErrorCodes::LOGIN_FAILED);
        }

        return $user;
    }

    /**
     * Validates registration data and creates a new user account.
     *
     * @param array $data Expects 'username', 'email', and 'password' keys.
     * @return User       The newly created and persisted user model.
     * @throws AuthException If validation fails or the account could not be saved.
     */
    public function register(array $data): User
    {
        if (!\filter_var($data['email'], \FILTER_VALIDATE_EMAIL)) {
            throw new AuthException(ErrorCodes::INVALID_EMAIL);
        }

        if ($this->repository->findByEmail($data['email'])) {
            throw new AuthException(ErrorCodes::EMAIL_ALREADY_EXISTS);
        }

        if ($this->repository->findByUsername($data['username'])) {
            throw new AuthException(ErrorCodes::USERNAME_ALREADY_TAKEN);
        }

        if (\strlen($data['password']) < 8) {
            throw new AuthException(ErrorCodes::WEAK_PASSWORD);
        }

        $user = new User();
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = \password_hash($data['password'], \PASSWORD_BCRYPT);
        $user->created_at = new \DateTime();
        $user->updated_at = new \DateTime();

        if (!$this->repository->save($user)) {
            throw new AuthException(ErrorCodes::REGISTRATION_FAILED);
        }

        return $user;
    }

    /**
     * Updates an existing user's profile data and optionally re-hashes their password.
     *
     * @param int   $id   The ID of the user to update.
     * @param array $data Expects 'username', 'email', and optionally 'password' keys.
     * @return User       The updated and persisted user model.
     * @throws AuthException If email validation fails or the email is already taken.
     * @throws \Exception    If the user is not found or the update fails.
     */
    public function updateUser(int $id, array $data): User
    {
        /** @var User */
        $user = $this->repository->find($id);
        if (!$user) {
            throw new \Exception('User not found.');
        }

        if (!\filter_var($data['email'], \FILTER_VALIDATE_EMAIL)) {
            throw new AuthException(ErrorCodes::INVALID_EMAIL);
        }

        $existing = $this->repository->findByEmail($data['email']);
        if ($existing && $existing->id !== $id) {
            throw new AuthException(ErrorCodes::EMAIL_ALREADY_EXISTS);
        }

        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->updated_at = new \DateTime();

        if (!empty($data['password'])) {
            $user->password = \password_hash($data['password'], \PASSWORD_BCRYPT);
        }

        if (!$this->repository->save($user)) {
            throw new \Exception('Failed to update user.');
        }

        return $user;
    }

    /**
     * Generates a new remember-me token, persists it, and returns it.
     *
     * @param User $user The user to generate the token for.
     * @return string    The generated remember-me token.
     */
    public function createRememberToken(User $user): string
    {
        $token = \bin2hex(\random_bytes(32));
        $user->remember_token = $token;
        $this->repository->save($user);

        return $token;
    }

    /**
     * Clears the remember-me token from the user record.
     *
     * @param User $user The user whose token should be removed.
     * @return void
     */
    public function clearRememberToken(User $user): void
    {
        $user->remember_token = null;
        $this->repository->save($user);
    }

    /**
     * Attempts to authenticate a user from a remember-me token.
     *
     * @param string $token The remember-me token from the cookie.
     * @return User|null    The matching user model, or null if the token is invalid.
     */
    public function loginFromToken(string $token): ?User
    {
        if (\method_exists($this->repository, 'findByRememberToken')) {
            return $this->repository->findByRememberToken($token);
        }
        return null;
    }

    /**
     * Generates a password reset token for the given email and persists it with a 1-hour expiry.
     *
     * @param string $email The email address of the account requesting a reset.
     * @return string|null  The generated reset token, or null if no account matches the email.
     */
    public function generateResetToken(string $email): ?string
    {
        $user = $this->repository->findByEmail($email);
        if (!$user) {
            return null;
        }

        $token = \bin2hex(\random_bytes(32));
        $user->reset_token = $token;
        $user->reset_expires_at = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');

        $this->repository->save($user);

        return $token;
    }

    /**
     * Validates a password reset token and checks that it has not expired.
     *
     * @param string $token The reset token to validate.
     * @return User|null    The matching user model, or null if the token is invalid or expired.
     */
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

    /**
     * Hashes and applies a new password for the user, then clears the reset token.
     *
     * @param User   $user        The user whose password is being reset.
     * @param string $newPassword The new plain-text password (minimum 8 characters).
     * @return bool               True on success, false on failure.
     * @throws AuthException      If the new password does not meet the minimum length requirement.
     */
    public function resetPassword(User $user, string $newPassword): bool
    {
        if (\strlen($newPassword) < 8) {
            throw new AuthException(ErrorCodes::WEAK_PASSWORD);
        }

        $user->password = \password_hash($newPassword, \PASSWORD_BCRYPT);
        $user->reset_token = null;
        $user->reset_expires_at = null;

        return $this->repository->save($user);
    }
}