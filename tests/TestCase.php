<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;

abstract class TestCase extends PHPUnitTestCase
{
    protected function getAppInstance(): App
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $container = require __DIR__ . '/../config/container.php';
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        
        (require __DIR__ . '/../config/routes.php')($app);
        
        $app->addRoutingMiddleware();

        return $app;
    }

    protected function createAppRequest(
        string $method,
        string $path,
        array $headers = [],
        array $serverParams = [],
        array $cookies = []
    ): Request {
        $uri = (new UriFactory())->createUri('http://localhost' . $path);
        
        // Bypass rate limiting for tests by generating a unique IP
        if (!isset($serverParams['REMOTE_ADDR'])) {
            $serverParams['REMOTE_ADDR'] = '127.0.0.' . rand(1, 250) . '.' . rand(1, 250);
        }
        
        $handle = fopen('php://temp', 'w+');
        $stream = (new \Slim\Psr7\Factory\StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }

    protected function runApp(string $method, string $path, array $parsedBody = []): \Psr\Http\Message\ResponseInterface
    {
        $app = $this->getAppInstance();

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $_SESSION['csrf_token'] = 'test-token';
            $parsedBody['csrf_token'] = 'test-token';
        }

        $request = $this->createAppRequest($method, $path);
        
        if (!empty($parsedBody)) {
            $request = $request->withParsedBody($parsedBody);
        }

        return $app->handle($request);
    }
}
