<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    /** @param array<string, string> $headers */
    public function __construct(
        private string $body = '',
        private int $statusCode = 200,
        private array $headers = [],
    ) {
    }

    public static function json(
        mixed $data,
        int $statusCode = 200,
        array $headers = [],
    ): self {
        $headers['Content-Type'] = 'application/json';

        return new self(
            body: json_encode($data, JSON_THROW_ON_ERROR),
            statusCode: $statusCode,
            headers: $headers,
        );
    }

    public static function apiSuccess(mixed $data, int $statusCode = 200): self
    {
        return self::json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'timestamp' => gmdate('c'),
                'version' => config('app.version', '1.0.0'),
            ],
        ], $statusCode);
    }

    public static function apiError(
        string $code,
        string $message,
        int $statusCode = 400,
        array $details = [],
    ): self {
        $payload = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== []) {
            $payload['error']['details'] = $details;
        }

        return self::json($payload, $statusCode);
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self(
            body: '',
            statusCode: $statusCode,
            headers: ['Location' => $url],
        );
    }

    public static function view(string $template, array $data = [], int $statusCode = 200): self
    {
        $viewPath = base_path('app/Views/' . $template . '.php');

        if (!file_exists($viewPath)) {
            return self::apiError('VIEW_NOT_FOUND', "View [{$template}] not found.", 500);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        return new self(
            body: $content,
            statusCode: $statusCode,
            headers: ['Content-Type' => 'text/html; charset=UTF-8'],
        );
    }

    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }
}
