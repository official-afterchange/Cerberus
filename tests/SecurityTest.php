<?php

declare(strict_types=1);

require_once __DIR__ . '/TestCase.php';

class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testSqlInjectionOnLogin(): void
    {
        $response = $this->runApp('POST', '/login', [
            'username' => "admin' OR '1'='1",
            'password' => "password123",
            'redirect' => ''
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
        $this->assertEmpty($_SESSION['user_id'] ?? null);
    }

    public function testSqlInjectionOnRegister(): void
    {
        $maliciousUsername = "user_" . time() . rand(1,999) . "'; DROP TABLE users;--";
        $uniqueHacker = 'hacker_' . time() . rand(1, 999) . '@test.com';
        
        $response = $this->runApp('POST', '/register', [
            'username' => $maliciousUsername,
            'email' => $uniqueHacker,
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/login', $response->getHeaderLine('Location'));
    }

    public function testXssInjectionOnProfileUpdate(): void
    {
        $xssPayload = "<script>alert('XSS')</script>";
        $uniqueEmail = 'xss_' . time() . rand(1, 999) . '@test.com';
        
        $this->runApp('POST', '/register', [
            'username' => $xssPayload,
            'email' => $uniqueEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $this->runApp('POST', '/login', [
            'username' => $xssPayload,
            'password' => 'password123',
            'redirect' => ''
        ]);

        $getResponse = $this->runApp('GET', '/profile');
        $body = (string) $getResponse->getBody();
        
        $this->assertStringNotContainsString("<script>alert('XSS')</script>", $body);
        $this->assertStringContainsString("&lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;", $body);
    }
}
