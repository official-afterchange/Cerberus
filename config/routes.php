<?php

declare(strict_types=1);

use Afterchange\Template\Controllers\ForgotPasswordController;
use Afterchange\Template\Controllers\HomeController;
use Afterchange\Template\Controllers\LangController;
use Afterchange\Template\Controllers\LoginController;
use Afterchange\Template\Controllers\OAuthController;
use Afterchange\Template\Controllers\ProfileController;
use Afterchange\Template\Controllers\RegisterController;
use Afterchange\Template\Controllers\ResetPasswordController;
use Afterchange\Template\Middlewares\AuthMiddleware;
use Afterchange\Template\Middlewares\CsrfMiddleware;
use Afterchange\Template\Middlewares\RateLimitMiddleware;
use Afterchange\Template\Middlewares\RememberMeMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->add(CsrfMiddleware::class);
    $app->add(RememberMeMiddleware::class);

    $app->get('/', [HomeController::class, 'index']);
    $app->get('/register', [RegisterController::class, 'show']);
    $app->post('/register', [RegisterController::class, 'register']);

    $app->get('/login', [LoginController::class, 'show']);
    $app->post('/login', [LoginController::class, 'login'])->add(RateLimitMiddleware::class);

    $app->get('/logout', [LoginController::class, 'logout']);
    $app->get('/lang/{lang}', [LangController::class, 'switch']);

    $app->get('/forgot-password', [ForgotPasswordController::class, 'show']);
    $app->post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])
        ->add(RateLimitMiddleware::class);

    $app->get('/reset-password', [ResetPasswordController::class, 'show']);
    $app->post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->add(RateLimitMiddleware::class);

    $app->get('/profile', [ProfileController::class, 'show'])
        ->add(AuthMiddleware::class);
    $app->post('/profile', [ProfileController::class, 'update'])->add(AuthMiddleware::class);

    $app->group('/oauth', function (RouteCollectorProxy $group): void {
        $group->get('/authorize', [OAuthController::class, 'showAuthorize'])->add(AuthMiddleware::class);
        $group->post('/authorize/confirm', [OAuthController::class, 'authorize'])->add(AuthMiddleware::class);
        $group->post('/authorize/deny', [OAuthController::class, 'deny'])->add(AuthMiddleware::class);

        $group->post('/token', [OAuthController::class, 'token']);
        $group->get('/userinfo', [OAuthController::class, 'userinfo']);
    });
};
