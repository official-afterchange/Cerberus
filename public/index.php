<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../config/container.php';

use Afterchange\Template\Exceptions\CustomErrorHandler;
use Afterchange\Template\Exceptions\CustomErrorRenderer;
use Slim\Factory\AppFactory;

AppFactory::setContainer($container);
$app = AppFactory::create();

(require __DIR__ . '/../config/routes.php')($app);

$app->addRoutingMiddleware();

$settings = $container->get('settings');

$errorMiddleware = $app->addErrorMiddleware(
    $settings['app']['debug'],
    true,
    true,
    $container->get(\Psr\Log\LoggerInterface::class)
);

// Register our handler — it handles OAuth 302 redirects then delegates
// to the renderer pipeline for everything else.
$errorHandler = new CustomErrorHandler(
    $app->getCallableResolver(),
    $app->getResponseFactory(),
    $container->get(\Psr\Log\LoggerInterface::class)
);

$errorHandler->registerErrorRenderer(
    'text/html',
    new CustomErrorRenderer($container->get(\Slim\Views\PhpRenderer::class))
);
$errorHandler->forceContentType('text/html');

$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->run();