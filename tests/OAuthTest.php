<?php

declare(strict_types=1);

require_once __DIR__ . '/TestCase.php';

class OAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testAuthorizeRequiresAuthentication(): void
    {
        // Un utilisateur non connecté doit être bloqué par AuthMiddleware
        // qui le redirige vers /login ou renvoie un 401/302 (selon l'implémentation de AuthMiddleware)
        $response = $this->runApp('GET', '/oauth/authorize?client_id=123');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login?redirect=%2Foauth%2Fauthorize%3Fclient_id%3D123', $response->getHeaderLine('Location'));
    }

    public function testAuthorizeWithInvalidClient(): void
    {
        // Simuler un utilisateur connecté
        $_SESSION['user_id'] = 1;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CLIENT_NOT_FOUND');

        $response = $this->runApp('GET', '/oauth/authorize?client_id=nonexistent');
    }

    public function testTokenEndpointRequiresValidGrantType(): void
    {
        $response = $this->runApp('POST', '/oauth/token', [
            'grant_type' => 'invalid_grant'
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('unsupported_grant_type', $body);
    }

    public function testUserInfoRequiresToken(): void
    {
        // Request sans Authorization: Bearer
        $response = $this->runApp('GET', '/oauth/userinfo');
        
        $this->assertEquals(401, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('invalid_token', $body);
    }
}
