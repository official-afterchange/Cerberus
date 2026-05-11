<?php

declare(strict_types=1);

namespace Afterchange\Template\Utils;

use Symfony\Component\Yaml\Yaml;

class Translator
{
    private array $messages = [];
    private array $fallbackMessages = [];

    public function __construct(string $langDir, string $lang = 'en')
    {
        $langFile = rtrim($langDir, '/') . '/' . $lang . '.yml';
        $fallbackFile = rtrim($langDir, '/') . '/en.yml';

        if (\file_exists($langFile)) {
            $this->messages = Yaml::parseFile($langFile) ?: [];
        }

        if ($lang !== 'en' && \file_exists($fallbackFile)) {
            $this->fallbackMessages = Yaml::parseFile($fallbackFile) ?: [];
        }
    }

    public function trans(?string $key): string
    {
        if ($key === null)
            return '';
        return $this->messages[$key] ?? $this->fallbackMessages[$key] ?? $key;
    }

    public function hasTrans(?string $key): bool
    {
        if ($key === null)
            return false;
        return isset($this->messages[$key]) || isset($this->fallbackMessages[$key]);
    }
}
