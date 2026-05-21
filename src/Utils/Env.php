<?php

declare(strict_types=1);

namespace Afterchange\Template\Utils;

/**
 * Provides typed access to environment variables with fallback default values.
 */
final class Env
{
    /**
     * Retrieves an environment variable, casting boolean and null string literals to their native types.
     *
     * @param string $key     The environment variable name.
     * @param mixed  $default The value to return if the variable is not set (default: null).
     * @return mixed          The typed value, or the default if the variable is absent.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        return match (\strtolower((string)$value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $value,
        };
    }
}