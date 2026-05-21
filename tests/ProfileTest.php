<?php

declare(strict_types=1);

require_once __DIR__ . '/TestCase.php';

class ProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear session before each test
        $_SESSION = [];
    }

    public function testProfileRequiresAuth(): void
    {
        $response = $this->runApp('GET', '/profile');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login?redirect=%2Fprofile', $response->getHeaderLine('Location'));
    }

    public function testProfileDisplayAuthenticated(): void
    {
        // Mock session
        $_SESSION['user_id'] = 1;

        $response = $this->runApp('GET', '/profile');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProfileUpdateSuccess(): void
    {
        $_SESSION['user_id'] = 1;

        $uniqueUser = 'admin_' . time();

        $response = $this->runApp('POST', '/profile', [
            'username' => $uniqueUser,
            'email' => $uniqueUser . '@admin.com',
            'password' => '', // Pas de changement de mot de passe
            'password_confirmation' => ''
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile', $response->getHeaderLine('Location'));
    }

    public function testProfileUpdateInvalidEmail(): void
    {
        $_SESSION['user_id'] = 1;

        $response = $this->runApp('POST', '/profile', [
            'username' => 'newname',
            'email' => 'invalid-email',
            'password' => '',
            'password_confirmation' => ''
        ]);

        // Validation returns back to /profile with flash error
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile', $response->getHeaderLine('Location'));
    }

    public function testProfileUpdateTakenEmail(): void
    {
        // On s'assure qu'un autre user existe
        $uniqueUser = 'other_' . time();
        $this->runApp('POST', '/register', [
            'username' => $uniqueUser,
            'email' => $uniqueUser . '@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $_SESSION['user_id'] = 1; // Un autre compte
        
        $response = $this->runApp('POST', '/profile', [
            'username' => 'mynameis1',
            'email' => $uniqueUser . '@test.com', // Trying to steal taken email
            'password' => '',
            'password_confirmation' => ''
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/profile', $response->getHeaderLine('Location'));
    }
}
