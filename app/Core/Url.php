<?php

declare(strict_types=1);

namespace App\Core;

class Url
{
    private static string $basePath = '';

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim(str_replace('\\', '/', $path), '/');
    }

    public static function base(): string
    {
        return self::$basePath;
    }

    public static function to(string $path = '/'): string
    {
        if ($path === '' || $path === '/') {
            return self::$basePath === '' ? '/' : self::$basePath . '/';
        }

        $path = str_starts_with($path, '/') ? $path : '/' . $path;

        return self::$basePath . $path;
    }
}
