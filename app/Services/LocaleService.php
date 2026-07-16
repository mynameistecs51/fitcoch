<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;

class LocaleService
{
    private static string $locale = 'th';

    /** @var array<string, mixed> */
    private static array $lines = [];

    public static function initFromRequest(?Request $request = null): void
    {
        $supported = config('locale.supported', ['en', 'th']);
        $default = (string) config('locale.default', 'en');

        $locale = $_SESSION['locale'] ?? null;

        if ($request !== null) {
            $queryLang = $request->query()['lang'] ?? null;
            if (is_string($queryLang) && in_array($queryLang, $supported, true)) {
                $locale = $queryLang;
            }
        }

        if ($locale === null) {
            $locale = $default;
        }

        if (!in_array($locale, $supported, true)) {
            $locale = $default;
        }

        self::set($locale);
    }

    public static function set(string $locale): void
    {
        $supported = config('locale.supported', ['en', 'th']);

        if (!in_array($locale, $supported, true)) {
            $locale = (string) config('locale.fallback', 'en');
        }

        self::$locale = $locale;
        $_SESSION['locale'] = $locale;
        self::load($locale);
    }

    public static function get(): string
    {
        return self::$locale;
    }

    /** @param array<string, string> $replace */
    public static function translate(string $key, array $replace = []): string
    {
        $value = self::lookup($key, self::$locale);

        if ($value === null) {
            $fallback = (string) config('locale.fallback', 'en');

            if ($fallback !== self::$locale) {
                self::load($fallback);
                $value = self::lookup($key, $fallback);
            }
        }

        if ($value === null) {
            return $key;
        }

        foreach ($replace as $name => $content) {
            $value = str_replace(':' . $name, (string) $content, $value);
        }

        return $value;
    }

    /** @return array<int, string> */
    public static function translateRoles(array $roles): array
    {
        return array_map(
            static fn (string $role): string => self::translate('roles.' . $role) !== 'roles.' . $role
                ? self::translate('roles.' . $role)
                : $role,
            $roles
        );
    }

    private static function load(string $locale): void
    {
        if (isset(self::$lines[$locale])) {
            return;
        }

        $path = base_path("lang/{$locale}.php");
        self::$lines[$locale] = file_exists($path) ? require $path : [];
    }

    private static function lookup(string $key, string $locale): ?string
    {
        self::load($locale);

        $segments = explode('.', $key);
        $value = self::$lines[$locale] ?? null;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return is_string($value) ? $value : null;
    }
}
