<?php

declare(strict_types=1);

use Afterchange\Template\Controllers\HomeController;
use Afterchange\Template\Controllers\LangController;
use Afterchange\Template\Controllers\ProfileController;
use Afterchange\Template\Controllers\ForgotPasswordController;
use Afterchange\Template\Controllers\ResetPasswordController;
use Afterchange\Template\Middlewares\AuthMiddleware;
use Afterchange\Template\Middlewares\CsrfMiddleware;
use Afterchange\Template\Middlewares\RateLimitMiddleware;
use Afterchange\Template\Middlewares\RememberMeMiddleware;
use Afterchange\Template\Repositories\PermissionRepository;
use Afterchange\Template\Repositories\RoleRepository;
use Afterchange\Template\Repositories\UserRepository;
use Afterchange\Template\Services\AuthService;
use Afterchange\Template\Services\PermissionService;
use Afterchange\Template\Services\RoleService;
use Afterchange\Template\Utils\Mailer;
use Afterchange\Template\Utils\Translator;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Slim\Views\PhpRenderer;
use Symfony\Component\Mailer\Transport;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    'settings' => require __DIR__ . '/settings.php',

    PhpRenderer::class => function ($c) {
        $settings = $c->get('settings');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return new PhpRenderer(__DIR__ . '/../views', [
            'title' => 'Template',
            'layout' => true,
            'extra_css' => '',
            'extra_js' => '',
            't' => $c->get(Translator::class),
            'csrf_token' => $_SESSION['csrf_token']
        ], 'layout.php');
    },

    PDO::class => function ($c) {
        $settings = $c->get('settings');
        $dsn = "mysql:host={$settings['db']['host']};dbname={$settings['db']['name']};port={$settings['db']['port']}";
        return new PDO($dsn, $settings['db']['user'], $settings['db']['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    },

    LoggerInterface::class => function ($c) {
        $settings = $c->get('settings');
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler($settings['log']['path'], Logger::DEBUG));
        return $logger;
    },

    UserRepository::class => function ($c) {
        return new UserRepository($c->get(PDO::class));
    },

    AuthService::class => function ($c) {
        return new AuthService($c->get(UserRepository::class));
    },

    Translator::class => function ($c) {
        $settings = $c->get('settings');
        $lang = $_SESSION['lang'] ?? $settings['app']['lang'] ?? 'en';
        return new Translator(__DIR__ . '/lang', $lang);
    },

    Mailer::class => function ($c) {
        $settings = $c->get('settings');
        $transport = Transport::fromDsn($settings['email']['dsn']);
        return new Mailer(new \Symfony\Component\Mailer\Mailer($transport));
    },

    ProfileController::class => function ($c) {
        return new ProfileController(
            $c->get(PhpRenderer::class),
            $c->get(AuthService::class),
            $c->get(Translator::class)
        );
    },

    HomeController::class => function ($c) {
        return new HomeController($c->get(PhpRenderer::class));
    },

    LangController::class => function ($c) {
        return new LangController($c->get(PhpRenderer::class));
    },

    ForgotPasswordController::class => function ($c) {
        return new ForgotPasswordController(
            $c->get(PhpRenderer::class),
            $c->get(AuthService::class),
            $c->get(\Psr\Log\LoggerInterface::class),
            $c->get(Translator::class),
            $c->get(Mailer::class)
        );
    },

    ResetPasswordController::class => function ($c) {
        return new ResetPasswordController(
            $c->get(PhpRenderer::class),
            $c->get(AuthService::class),
            $c->get(Translator::class)
        );
    },

    AuthMiddleware::class => function () {
        return new AuthMiddleware();
    },

    RateLimitMiddleware::class => function ($c) {
        return new RateLimitMiddleware($c->get(Translator::class));
    },

    CsrfMiddleware::class => function () {
        return new CsrfMiddleware();
    },

    RememberMeMiddleware::class => function ($c) {
        return new RememberMeMiddleware($c->get(AuthService::class));
    },
]);

return $containerBuilder->build();
