<?php

declare(strict_types=1);

use Afterchange\Template\Middlewares\AuthMiddleware;
use Afterchange\Template\Middlewares\CsrfMiddleware;
use Afterchange\Template\Middlewares\RateLimitMiddleware;
use Afterchange\Template\Middlewares\RememberMeMiddleware;
use Slim\App;

return function (App $app): void {
    $app->add(CsrfMiddleware::class);
    $app->add(RememberMeMiddleware::class);

    $app->get('/', [Afterchange\Template\Controllers\HomeController::class, 'index']);
    $app->get('/register', [Afterchange\Template\Controllers\RegisterController::class, 'show']);
    $app->post('/register', [Afterchange\Template\Controllers\RegisterController::class, 'register']);

    $app->get('/login', [Afterchange\Template\Controllers\LoginController::class, 'show']);
    $app->post('/login', [Afterchange\Template\Controllers\LoginController::class, 'login'])->add(RateLimitMiddleware::class);

    $app->get('/logout', [Afterchange\Template\Controllers\LoginController::class, 'logout']);
    $app->get('/lang/{lang}', [Afterchange\Template\Controllers\LangController::class, 'switch']);

    $app->get('/forgot-password', [Afterchange\Template\Controllers\ForgotPasswordController::class, 'show']);
    $app->post('/forgot-password', [Afterchange\Template\Controllers\ForgotPasswordController::class, 'sendLink'])
        ->add(RateLimitMiddleware::class);

    $app->get('/reset-password', [Afterchange\Template\Controllers\ResetPasswordController::class, 'show']);
    $app->post('/reset-password', [Afterchange\Template\Controllers\ResetPasswordController::class, 'reset'])
        ->add(RateLimitMiddleware::class);

    $app->get('/profile', [Afterchange\Template\Controllers\ProfileController::class, 'show'])
        ->add(AuthMiddleware::class);
    $app->post('/profile', [Afterchange\Template\Controllers\ProfileController::class, 'update'])->add(AuthMiddleware::class);

    $app->group('/oauth', function () use ($app) {
        $app->get('/authorize', [Afterchange\Template\Controllers\OAuthController::class, 'authorize']);
        $app->post('/authorize', [Afterchange\Template\Controllers\OAuthController::class, 'authorize']);
        $app->post('/token', [Afterchange\Template\Controllers\OAuthController::class, 'token']);
    });
};
