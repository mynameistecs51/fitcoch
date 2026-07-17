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

    public function authenticate(string $loginId, string $password, bool $remember = false): User
    {
        $user = $this->findUserByLoginId($loginId);

        if ($user === null) {
            throw new Exception(__('errors.invalid_credentials'));
        }

        if ($user->status === 'suspended') {
            throw new Exception(__('errors.account_suspended'));
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new Exception(__('errors.invalid_credentials'));
        }

        $this->startSession($user, $remember);

        return $user;
    }

    /**
     * @param array{
     *     student_id: string,
     *     title_prefix: string,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     password: string,
     *     password_confirmation?: string
     * } $data
     */
    public function register(array $data): User
    {
        $errors = $this->validateRegistration($data);

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        $studentId = $this->normalizeStudentId($data['student_id']);
        $email = strtolower(trim($data['email']));

        if ($this->userRepo->studentIdExists($studentId)) {
            throw new ValidationException(__('errors.validation_failed'), [
                'student_id' => [__('validation.student_id_taken')],
            ]);
        }

        if ($this->userRepo->emailExists($email)) {
            throw new ValidationException(__('errors.validation_failed'), [
                'email' => [__('validation.email_taken')],
            ]);
        }

        $user = $this->userRepo->create([
            'student_id' => $studentId,
            'title_prefix' => trim($data['title_prefix']),
            'email' => $email,
            'password_hash' => password_hash($data['password'], PASSWORD_ARGON2ID),
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'timezone' => default_timezone(),
        ]);

        $this->roleRepo->assignRole($user->id, 'learner');

        $this->startSession($user, false);

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

    public function isActiveWebSession(int $userId): bool
    {
        $sessionToken = $_SESSION['session_token'] ?? null;

        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        $storedToken = $this->userRepo->findSessionToken($userId);

        return $storedToken !== null && hash_equals($storedToken, $sessionToken);
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

        if ($this->resolveLoginId($data) === '') {
            $errors['login'] = [__('validation.login_required')];
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

        $studentId = $this->normalizeStudentId((string) ($data['student_id'] ?? ''));

        if ($studentId === '') {
            $errors['student_id'] = [__('validation.student_id_required')];
        } elseif (!preg_match('/^[A-Za-z0-9_-]{3,20}$/', $studentId)) {
            $errors['student_id'] = [__('validation.student_id_invalid')];
        }

        if (empty($data['title_prefix'])) {
            $errors['title_prefix'] = [__('validation.title_prefix_required')];
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = [__('validation.first_name_required')];
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = [__('validation.last_name_required')];
        }

        $email = strtolower(trim((string) ($data['email'] ?? '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = [__('validation.email_required')];
        }

        $passwordErrors = $this->validatePassword((string) ($data['password'] ?? ''));

        if ($passwordErrors !== []) {
            $errors['password'] = $passwordErrors;
        }

        $password = (string) ($data['password'] ?? '');
        $passwordConfirmation = (string) ($data['password_confirmation'] ?? '');

        if ($passwordConfirmation === '') {
            $errors['password_confirmation'] = [__('validation.password_confirmation_required')];
        } elseif ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = [__('auth.password_confirmation_mismatch')];
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

    /** @param array<string, mixed> $data */
    public function resolveLoginId(array $data): string
    {
        return trim((string) ($data['login'] ?? $data['student_id'] ?? $data['email'] ?? ''));
    }

    private function findUserByLoginId(string $loginId): ?User
    {
        $loginId = trim($loginId);

        if ($loginId === '') {
            return null;
        }

        $user = $this->userRepo->findByStudentId($this->normalizeStudentId($loginId));

        if ($user !== null) {
            return $user;
        }

        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            return $this->userRepo->findByEmail(strtolower($loginId));
        }

        return null;
    }

    private function normalizeStudentId(string $studentId): string
    {
        return strtoupper(trim($studentId));
    }

    private function syntheticEmailForStudentId(string $studentId): string
    {
        return strtolower($studentId) . '@student.fitcoch.local';
    }

    private function startSession(User $user, bool $remember = false): void
    {
        $sessionToken = bin2hex(random_bytes(32));
        $this->userRepo->updateSessionToken($user->id, $sessionToken);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['last_activity'] = time();
        $_SESSION['remember_me'] = $remember;

        if ($remember) {
            $lifetime = 60 * 60 * 24 * 30;
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), [
                'expires' => time() + $lifetime,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => (bool) $params['secure'],
                'httponly' => (bool) $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
    }
}
