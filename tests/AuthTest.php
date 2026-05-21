<?php

declare(strict_types=1);

require_once __DIR__ . '/TestCase.php';

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testRegisterSuccess(): void
    {
        $uniqueUser = 'testuser_' . time();
        $response = $this->runApp('POST', '/register', [
            'username' => $uniqueUser,
            'email' => $uniqueUser . '@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function testRegisterInvalidEmail(): void
    {
        $response = $this->runApp('POST', '/register', [
            'username' => 'invalid_email_user',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/register', $response->getHeaderLine('Location'));
    }

    public function testRegisterWeakPassword(): void
    {
        $response = $this->runApp('POST', '/register', [
            'username' => 'weakpass_user',
            'email' => 'weakpass@test.com',
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/register', $response->getHeaderLine('Location'));
    }

    public function testRegisterTakenUsername(): void
    {
        $unique = 'taken_' . time() . rand(1, 999);
        // First Register
        $this->runApp('POST', '/register', [
            'username' => $unique,
            'email' => $unique . '@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // Attempt Duplicate
        $response = $this->runApp('POST', '/register', [
            'username' => $unique,
            'email' => $unique . '_2@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/register', $response->getHeaderLine('Location'));
    }

    public function testLoginSuccess(): void
    {
        // Création unique d'un utilisateur pour ce test
        $uniqueUser = 'login_' . time() . rand(100, 999);
        $this->runApp('POST', '/register', [
            'username' => $uniqueUser,
            'email' => $uniqueUser . '@admin.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response = $this->runApp('POST', '/login', [
            'username' => $uniqueUser,
            'password' => 'password123',
            'redirect' => ''
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile', $response->getHeaderLine('Location'));
        
        $this->assertNotEmpty($_SESSION['user_id'] ?? null);
    }

    public function testLoginFailure(): void
    {
        $response = $this->runApp('POST', '/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
            'redirect' => ''
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
        $this->assertEmpty($_SESSION['user_id'] ?? null);
    }

    public function testLogout(): void
    {
        $_SESSION['user_id'] = 1;

        $response = $this->runApp('GET', '/logout');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
        
        $this->assertEmpty($_SESSION['user_id'] ?? null);
    }
}
