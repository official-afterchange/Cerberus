<?php

declare(strict_types=1);

namespace Afterchange\Template\Middlewares;

use Afterchange\Template\Utils\ErrorCodes;
use Afterchange\Template\Utils\Translator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

/**
 * Limits repeated requests from the same IP using a file-based attempt counter.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    private string $storagePath;
    private int $maxAttempts;
    private int $decaySeconds;
    private Translator $translator;

    /**
     * Initializes the middleware and ensures the rate limit storage directory exists.
     *
     * @param Translator $translator   Translation utility for user-facing error messages.
     * @param string     $storagePath  Path to the directory used to store per-IP attempt files.
     * @param int        $maxAttempts  Maximum number of attempts allowed within the decay window (default: 5).
     * @param int        $decaySeconds Duration in seconds before the attempt counter resets (default: 60).
     */
    public function __construct(Translator $translator, string $storagePath = __DIR__ . '/../../storage/rate_limit', int $maxAttempts = 5, int $decaySeconds = 60)
    {
        $this->translator = $translator;
        $this->storagePath = $storagePath;
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;

        if (!\is_dir($this->storagePath)) {
            @\mkdir($this->storagePath, 0775, true);
        }
    }

    /**
     * Tracks request attempts per IP, blocks requests that exceed the limit, and clears
     * the counter on successful authentication.
     *
     * @param Request $request The incoming HTTP request.
     * @param Handler $handler The next middleware or route handler in the pipeline.
     * @return Response        The handler's response, or a 302 redirect to login if the limit is exceeded.
     */
    public function process(Request $request, Handler $handler): Response
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $filename = $this->storagePath . '/' . \md5($ip) . '.json';
        $now = \time();

        $data = ['attempts' => 0, 'last_attempt' => 0];

        if (\file_exists($filename)) {
            $content = \file_get_contents($filename);
            if ($content !== false) {
                $decoded = \json_decode($content, true);
                if (\is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }

        // Reset the counter if the decay window has elapsed
        if ($now - $data['last_attempt'] > $this->decaySeconds) {
            $data['attempts'] = 0;
        }

        if ($data['attempts'] >= $this->maxAttempts) {
            $_SESSION['flash_error'] = $this->translator->trans(ErrorCodes::TOO_MANY_REQUESTS);
            $response = new SlimResponse();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        if (!\is_dir($this->storagePath)) {
            $_SESSION['flash_error'] = $this->translator->trans(ErrorCodes::INTERNAL_ERROR);
            $response = new SlimResponse();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $data['attempts']++;
        $data['last_attempt'] = $now;

        @\file_put_contents($filename, \json_encode($data));

        $response = $handler->handle($request);

        // Clear the attempt file on successful login
        if (isset($_SESSION['user_id']) && $response->getStatusCode() === 302) {
            if (\file_exists($filename)) {
                @\unlink($filename);
            }
        }

        return $response;
    }
}