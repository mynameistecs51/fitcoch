<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;
use Exception;

class PasswordResetService
{
    private const TOKEN_TTL_SECONDS = 3600;

    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly PasswordResetRepository $resetRepo,
    ) {
    }

    /**
     * @return array{message: string, reset_url: ?string}
     */
    public function requestReset(string $email): array
    {
        $email = strtolower(trim($email));
        $user = $this->userRepo->findByEmail($email);
        $resetUrl = null;

        if ($user !== null) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = gmdate('Y-m-d H:i:s', time() + self::TOKEN_TTL_SECONDS);

            $this->resetRepo->create($user->id, $tokenHash, $expiresAt);
            $resetUrl = url('/reset-password?token=' . urlencode($token));

            $this->sendResetEmail($user->email, $resetUrl);
        }

        return [
            'message' => __('auth.forgot_password_sent'),
            'reset_url' => config('app.debug') ? $resetUrl : null,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function resetPassword(string $token, array $data): void
    {
        $token = trim($token);

        if ($token === '') {
            throw new ValidationException(__('errors.validation_failed'), [
                'token' => [__('auth.reset_token_invalid')],
            ]);
        }

        $record = $this->resetRepo->findValidByTokenHash(hash('sha256', $token));

        if ($record === null) {
            throw new ValidationException(__('errors.validation_failed'), [
                'token' => [__('auth.reset_token_invalid')],
            ]);
        }

        $passwordErrors = $this->validatePassword((string) ($data['password'] ?? ''));

        if ($passwordErrors !== []) {
            throw new ValidationException(__('errors.validation_failed'), [
                'password' => $passwordErrors,
            ]);
        }

        $confirm = (string) ($data['password_confirmation'] ?? '');

        if ($confirm !== (string) ($data['password'] ?? '')) {
            throw new ValidationException(__('errors.validation_failed'), [
                'password_confirmation' => [__('auth.password_confirmation_mismatch')],
            ]);
        }

        $this->userRepo->updatePassword(
            (int) $record['user_id'],
            password_hash((string) $data['password'], PASSWORD_ARGON2ID)
        );
        $this->resetRepo->markUsed((int) $record['id']);
    }

    public function isTokenValid(string $token): bool
    {
        $token = trim($token);

        if ($token === '') {
            return false;
        }

        return $this->resetRepo->findValidByTokenHash(hash('sha256', $token)) !== null;
    }

    /** @return array<int, string> */
    private function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 10) {
            $errors[] = __('validation.password_min');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = __('validation.password_upper');
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = __('validation.password_lower');
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = __('validation.password_number');
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = __('validation.password_special');
        }

        return $errors;
    }

    private function sendResetEmail(string $email, string $resetUrl): void
    {
        $subject = __('auth.reset_email_subject');
        $body = __('auth.reset_email_body', ['url' => $resetUrl]);
        $headers = 'Content-Type: text/plain; charset=UTF-8';

        @mail($email, $subject, $body, $headers);

        $logPath = config('storage.logs') . '/password_resets.log';
        file_put_contents(
            $logPath,
            sprintf("[%s] reset link for %s: %s\n", gmdate('c'), $email, $resetUrl),
            FILE_APPEND
        );
    }
}
