<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../config/container.php';

use Slim\Factory\AppFactory;

AppFactory::setContainer($container);
$app = AppFactory::create();

(require __DIR__ . '/../config/routes.php')($app);

$app->addRoutingMiddleware();

$settings = $container->get('settings');

if ($settings['app']['debug']) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

error_reporting(E_ALL);

$errorMiddleware = $app->addErrorMiddleware(
    $settings['app']['debug'],
    true,
    true,
    $container->get(\Psr\Log\LoggerInterface::class)
);

$errorHandler = $errorMiddleware->getDefaultErrorHandler();

$errorHandler->registerErrorRenderer(
    'text/html', 
    new \Afterchange\Template\Exceptions\CustomErrorRenderer(
        $container->get(\Slim\Views\PhpRenderer::class)
    )
);

$errorHandler->forceContentType('text/html');

$app->run();