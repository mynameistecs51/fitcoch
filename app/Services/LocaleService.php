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
        self::set('th');
    }

    public static function set(string $locale): void
    {
        self::$locale = 'th';
        $_SESSION['locale'] = 'th';
        self::load('th');
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
