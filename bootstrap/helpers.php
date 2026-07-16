<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];

        $parts = explode('.', $key);
        $file = $parts[0];

        if (!isset($configs[$file])) {
            $path = dirname(__DIR__) . "/config/{$file}.php";
            $configs[$file] = file_exists($path) ? require $path : [];
        }

        $value = $configs[$file];

        for ($i = 1, $count = count($parts); $i < $count; $i++) {
            if (!is_array($value) || !array_key_exists($parts[$i], $value)) {
                return $default;
            }
            $value = $value[$parts[$i]];
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);

        return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }
}

if (!function_exists('escape')) {
    function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(?string $token): bool
    {
        return isset($_SESSION['csrf_token'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('__')) {
    /** @param array<string, string> $replace */
    function __(string $key, array $replace = []): string
    {
        return \App\Services\LocaleService::translate($key, $replace);
    }
}

if (!function_exists('locale')) {
    function locale(): string
    {
        return \App\Services\LocaleService::get();
    }
}

if (!function_exists('translate_roles')) {
    /** @param array<int, string> $roles */
    function translate_roles(array $roles): string
    {
        return implode(', ', \App\Services\LocaleService::translateRoles($roles));
    }
}

if (!function_exists('base_url')) {
    function base_url(): string
    {
        return \App\Core\Url::base();
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        return \App\Core\Url::to($path);
    }
}

if (!function_exists('default_timezone')) {
    function default_timezone(): string
    {
        return (string) config('app.default_timezone', 'Asia/Bangkok');
    }
}

if (!function_exists('timezone_options')) {
    /** @return array<string, string> */
    function timezone_options(): array
    {
        $preferred = [
            'Asia/Bangkok',
            'Asia/Singapore',
            'Asia/Jakarta',
            'Asia/Ho_Chi_Minh',
            'Asia/Manila',
            'Asia/Tokyo',
            'Asia/Seoul',
            'Asia/Shanghai',
            'Asia/Kolkata',
            'UTC',
            'Europe/London',
            'America/New_York',
            'America/Los_Angeles',
        ];

        $options = [];

        foreach ($preferred as $timezone) {
            if (in_array($timezone, timezone_identifiers_list(), true)) {
                $options[$timezone] = $timezone;
            }
        }

        return $options;
    }
}

if (!function_exists('is_valid_timezone')) {
    function is_valid_timezone(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true);
    }
}
