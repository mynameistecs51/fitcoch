<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Exception;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly RoleRepository $roleRepo,
        private readonly AuthorizationService $authzService,
        private readonly JwtService $jwtService,
    ) {
    }

    public function authenticate(string $email, string $password): User
    {
        $user = $this->userRepo->findByEmail($email);

        if ($user === null) {
            throw new Exception(__('errors.invalid_credentials'));
        }

        if ($user->status === 'suspended') {
            throw new Exception(__('errors.account_suspended'));
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new Exception(__('errors.invalid_credentials'));
        }

        $this->startSession($user);

        return $user;
    }

    /** @param array{email: string, password: string, first_name: string, last_name: string, timezone?: string} $data */
    public function register(array $data): User
    {
        $errors = $this->validateRegistration($data);

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        if ($this->userRepo->emailExists($data['email'])) {
            throw new ValidationException(__('errors.validation_failed'), [
                'email' => [__('validation.email_taken')],
            ]);
        }

        $user = $this->userRepo->create([
            'email' => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_ARGON2ID),
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'timezone' => default_timezone(),
        ]);

        $this->roleRepo->assignRole($user->id, 'learner');

        $this->startSession($user);

        return $user;
    }

    public function issueToken(User $user): string
    {
        return $this->jwtService->encode($user->id, $user->email);
    }

    public function logout(?string $bearerToken = null): void
    {
        if ($bearerToken !== null) {
            try {
                $payload = $this->jwtService->decode($bearerToken);
                $this->jwtService->revoke($payload['jti'], $payload['exp']);
            } catch (Exception) {
                // Token already invalid; still clear session below.
            }
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
    }

    public function userFromToken(string $token): ?User
    {
        try {
            $payload = $this->jwtService->decode($token);

            return $this->userRepo->findById($payload['sub']);
        } catch (Exception) {
            return null;
        }
    }

    public function currentUser(): ?User
    {
        if (isset($_SESSION['user_id'])) {
            return $this->userRepo->findById((int) $_SESSION['user_id']);
        }

        return null;
    }

    /** @return array<int, string> */
    public function getUserRoles(int $userId): array
    {
        return $this->authzService->getUserRoles($userId);
    }

    /** @return array<string, array<int, string>> */
    public function validateLoginInput(array $data): array
    {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = [__('validation.email_required')];
        }

        if (empty($data['password'])) {
            $errors['password'] = [__('validation.password_required')];
        }

        return $errors;
    }

    /** @return array<string, array<int, string>> */
    private function validateRegistration(array $data): array
    {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = [__('validation.email_required')];
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = [__('validation.first_name_required')];
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = [__('validation.last_name_required')];
        }

        $passwordErrors = $this->validatePassword((string) ($data['password'] ?? ''));

        if ($passwordErrors !== []) {
            $errors['password'] = $passwordErrors;
        }

        return $errors;
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

    private function startSession(User $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['last_activity'] = time();
    }
}
