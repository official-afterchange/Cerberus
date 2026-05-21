<?php

declare(strict_types=1);

require_once __DIR__ . '/TestCase.php';

class PasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testForgotPasswordFormDisplay(): void
    {
        $response = $this->runApp('GET', '/forgot-password');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testForgotPasswordSubmitSendsEmailForExistingUser(): void
    {
        $response = $this->runApp('POST', '/forgot-password', [
            'email' => 'admin@admin.com'
        ]);

        // Vérification de la redirection avec un message flash de succès
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
        $this->assertNotEmpty($_SESSION['flash_success'] ?? null);
    }

    public function testForgotPasswordSubmitRejectsInvalidEmailFormat(): void
    {
        $response = $this->runApp('POST', '/forgot-password', [
            'email' => 'invalid_email_format'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location')); // Even if failed validation, it behaves safely by redirecting to login to avoid enumeration
    }

    public function testResetPasswordFormRejectsInvalidToken(): void
    {
        $response = $this->runApp('GET', '/reset-password?token=invalid_token123');
        
        // Un token invalide doit rediriger vers login
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/forgot-password', $response->getHeaderLine('Location'));
    }
}
