<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../config/container.php';

use Slim\Factory\AppFactory;

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addRoutingMiddleware();

(require __DIR__ . '/../config/routes.php')($app);

$settings = $container->get('settings');
$errorMiddleware = $app->addErrorMiddleware(
    $settings['app']['debug'], 
    true, 
    true, 
    $container->get(\Psr\Log\LoggerInterface::class)
);

$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->registerErrorRenderer('text/html', new \Afterchange\Template\Exceptions\CustomErrorRenderer($container->get(\Slim\Views\PhpRenderer::class)));

$app->run();