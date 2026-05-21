<?php

declare(strict_types=1);

namespace Afterchange\Template\Utils;

use Symfony\Component\Yaml\Yaml;

/**
 * Loads YAML translation files and resolves message keys with English fallback support.
 */
final class Translator
{
    private array $messages = [];
    private array $fallbackMessages = [];

    /**
     * Loads the translation file for the requested language and the English fallback if needed.
     *
     * @param string $langDir The directory containing the YAML translation files.
     * @param string $lang    The target language code (default: 'en').
     */
    public function __construct(string $langDir, string $lang = 'en')
    {
        $langFile = \rtrim($langDir, '/') . '/' . $lang . '.yml';
        $fallbackFile = \rtrim($langDir, '/') . '/en.yml';

        if (\file_exists($langFile)) {
            $this->messages = Yaml::parseFile($langFile) ?: [];
        }

        if ($lang !== 'en' && \file_exists($fallbackFile)) {
            $this->fallbackMessages = Yaml::parseFile($fallbackFile) ?: [];
        }
    }

    /**
     * Resolves a translation key, falling back to English then to the raw key if no match is found.
     *
     * @param string|null $key The translation key to resolve.
     * @return string          The translated string, or the key itself if no translation exists.
     */
    public function trans(?string $key): string
    {
        if ($key === null) {
            return '';
        }
        return $this->messages[$key] ?? $this->fallbackMessages[$key] ?? $key;
    }

    /**
     * Checks whether a translation exists for the given key in either the active or fallback language.
     *
     * @param string|null $key The translation key to check.
     * @return bool            True if a translation exists, false otherwise.
     */
    public function hasTrans(?string $key): bool
    {
        if ($key === null) {
            return false;
        }
        return isset($this->messages[$key]) || isset($this->fallbackMessages[$key]);
    }
}