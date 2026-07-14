<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Exception;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly JwtService $jwtService,
    ) {
    }

    public function authenticate(string $email, string $password): User
    {
        $user = $this->userRepo->findByEmail($email);

        if ($user === null) {
            throw new Exception('Invalid login credentials.');
        }

        if ($user->status === 'suspended') {
            throw new Exception('Account has been suspended.');
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new Exception('Invalid login credentials.');
        }

        $this->startSession($user);

        return $user;
    }

    /** @param array{email: string, password: string, first_name: string, last_name: string, timezone?: string} $data */
    public function register(array $data): User
    {
        $errors = $this->validateRegistration($data);

        if ($errors !== []) {
            throw new ValidationException('The provided inputs failed validation requirements.', $errors);
        }

        if ($this->userRepo->emailExists($data['email'])) {
            throw new ValidationException('The provided inputs failed validation requirements.', [
                'email' => ['The email address has already been registered.'],
            ]);
        }

        $user = $this->userRepo->create([
            'email' => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_ARGON2ID),
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'timezone' => $data['timezone'] ?? 'UTC',
        ]);

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

    /** @return array<string, array<int, string>> */
    public function validateLoginInput(array $data): array
    {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['A valid email address is required.'];
        }

        if (empty($data['password'])) {
            $errors['password'] = ['Password is required.'];
        }

        return $errors;
    }

    /** @return array<string, array<int, string>> */
    private function validateRegistration(array $data): array
    {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['A valid email address is required.'];
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = ['First name is required.'];
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = ['Last name is required.'];
        }

        $passwordErrors = $this->validatePassword((string) ($data['password'] ?? ''));

        if ($passwordErrors !== []) {
            $errors['password'] = $passwordErrors;
        }

        if (!empty($data['timezone']) && !in_array($data['timezone'], timezone_identifiers_list(), true)) {
            $errors['timezone'] = ['The provided timezone is invalid.'];
        }

        return $errors;
    }

    /** @return array<int, string> */
    private function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 10) {
            $errors[] = 'Password must be at least 10 characters.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
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
