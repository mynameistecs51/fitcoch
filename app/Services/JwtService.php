<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

class JwtService
{
    public function encode(int $userId, string $email): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + (int) config('app.jwt_ttl', 3600);

        $payload = [
            'iss' => config('app.name', 'FMMP'),
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'sub' => $userId,
            'email' => $email,
            'jti' => bin2hex(random_bytes(16)),
        ];

        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_THROW_ON_ERROR));
        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$body}", $this->secret(), true)
        );

        return "{$header}.{$body}.{$signature}";
    }

    /** @return array{sub: int, email: string, exp: int, jti: string} */
    public function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Invalid token format.');
        }

        [$header, $body, $signature] = $parts;

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$body}", $this->secret(), true)
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw new Exception('Invalid token signature.');
        }

        $payload = json_decode($this->base64UrlDecode($body), true);

        if (!is_array($payload)) {
            throw new Exception('Invalid token payload.');
        }

        if (($payload['exp'] ?? 0) < time()) {
            throw new Exception('Token has expired.');
        }

        if ($this->isRevoked((string) ($payload['jti'] ?? ''))) {
            throw new Exception('Token has been revoked.');
        }

        return [
            'sub' => (int) ($payload['sub'] ?? 0),
            'email' => (string) ($payload['email'] ?? ''),
            'exp' => (int) ($payload['exp'] ?? 0),
            'jti' => (string) ($payload['jti'] ?? ''),
        ];
    }

    public function revoke(string $jti, int $expiresAt): void
    {
        $revoked = $this->loadRevokedTokens();
        $revoked[$jti] = $expiresAt;
        $this->saveRevokedTokens($revoked);
    }

    private function isRevoked(string $jti): bool
    {
        if ($jti === '') {
            return true;
        }

        $revoked = $this->loadRevokedTokens();
        $expiresAt = $revoked[$jti] ?? null;

        if ($expiresAt === null) {
            return false;
        }

        if ($expiresAt < time()) {
            unset($revoked[$jti]);
            $this->saveRevokedTokens($revoked);

            return false;
        }

        return true;
    }

    /** @return array<string, int> */
    private function loadRevokedTokens(): array
    {
        $path = $this->revokedTokensPath();

        if (!file_exists($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    /** @param array<string, int> $tokens */
    private function saveRevokedTokens(array $tokens): void
    {
        $path = $this->revokedTokensPath();
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($tokens, JSON_THROW_ON_ERROR));
    }

    private function revokedTokensPath(): string
    {
        return config('storage.cache') . '/revoked_tokens.json';
    }

    private function secret(): string
    {
        return (string) config('app.jwt_secret');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;

        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($value, '-_', '+/'), true);
    }
}
