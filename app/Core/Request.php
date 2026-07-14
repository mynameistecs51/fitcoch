<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    /** @var array<string, mixed> */
    private array $attributes = [];

    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        /** @var array<string, string> */
        private readonly array $headers,
        /** @var array<string, mixed> */
        private readonly array $query,
        /** @var array<string, mixed> */
        private readonly array $body,
        private readonly string $rawBody,
        /** @var array<string, mixed> */
        private readonly array $files = [],
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath !== '' && $basePath !== '/' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = (string) $value;
            }
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = (string) $_SERVER['CONTENT_TYPE'];
        }

        $rawBody = file_get_contents('php://input') ?: '';
        $body = $_POST;

        if (str_contains($headers['Content-Type'] ?? '', 'application/json') && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        return new self($method, $uri, $headers, $_GET, $body, $rawBody, $_FILES);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function isApi(): bool
    {
        return str_starts_with($this->uri, '/api/v1');
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }

        return $default;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('Authorization');

        if ($authorization !== null && preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function query(): array
    {
        return $this->query;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->body;
    }

    public function json(): array
    {
        if ($this->rawBody === '') {
            return [];
        }

        $decoded = json_decode($this->rawBody, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<string, mixed> */
    public function files(): array
    {
        return $this->files;
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
