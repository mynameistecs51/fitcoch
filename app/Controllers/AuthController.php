<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\LearnerDashboardService;
use App\Services\PasswordResetService;
use App\Services\ValidationException;
use Exception;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly PasswordResetService $passwordResetService,
        private readonly LearnerDashboardService $learnerDashboardService,
    ) {
    }

    public function showLogin(Request $request): Response
    {
        if ($this->authService->currentUser() !== null) {
            return Response::redirect('/dashboard');
        }

        $error = $request->query()['error'] ?? null;

        if ($error === 'session_replaced') {
            $error = __('auth.session_replaced');
        }

        return Response::view('auth/login', [
            'title' => __('auth.sign_in'),
            'error' => $error,
            'success' => $request->query()['success'] ?? null,
        ]);
    }

    public function login(Request $request): Response
    {
        $data = $request->isApi() ? $request->json() : $request->all();

        if (!$request->isApi() && !verify_csrf_token($data['csrf_token'] ?? null)) {
            return Response::view('auth/login', [
                'title' => __('auth.sign_in'),
                'error' => __('errors.invalid_csrf'),
                'email' => $data['email'] ?? '',
            ]);
        }

        $errors = $this->authService->validateLoginInput($data);

        if ($errors !== []) {
            return $this->respondWithValidationErrors($request, $errors, 'login');
        }

        try {
            $user = $this->authService->authenticate(
                (string) $data['email'],
                (string) $data['password']
            );
        } catch (Exception $e) {
            return $this->respondWithAuthFailure($request, $e->getMessage(), $data);
        }

        if ($request->isApi()) {
            return Response::apiSuccess([
                'access_token' => $this->authService->issueToken($user),
                'token_type' => 'Bearer',
                'expires_in' => (int) config('app.jwt_ttl', 3600),
                'user' => $user->toPublicArray($this->authService->getUserRoles($user->id)),
            ]);
        }

        return Response::redirect('/dashboard');
    }

    public function showRegister(Request $request): Response
    {
        if ($this->authService->currentUser() !== null) {
            return Response::redirect('/dashboard');
        }

        return Response::view('auth/register', [
            'title' => __('auth.create_account'),
            'error' => $request->query()['error'] ?? null,
        ]);
    }

    public function register(Request $request): Response
    {
        $data = $request->isApi() ? $request->json() : $request->all();

        if (!$request->isApi() && !verify_csrf_token($data['csrf_token'] ?? null)) {
            return Response::view('auth/register', [
                'title' => __('auth.create_account'),
                'error' => __('errors.invalid_csrf'),
                'form' => $data,
            ]);
        }

        try {
            $user = $this->authService->register($data);
        } catch (ValidationException $e) {
            return $this->respondWithValidationErrors($request, $e->errors(), 'register', $data);
        }

        if ($request->isApi()) {
            return Response::apiSuccess([
                'user' => $user->toPublicArray($this->authService->getUserRoles($user->id)),
                'access_token' => $this->authService->issueToken($user),
                'token_type' => 'Bearer',
                'expires_in' => (int) config('app.jwt_ttl', 3600),
            ], 201);
        }

        return Response::redirect('/dashboard');
    }

    public function showForgotPassword(Request $request): Response
    {
        if ($this->authService->currentUser() !== null) {
            return Response::redirect('/dashboard');
        }

        return Response::view('auth/forgot-password', [
            'title' => __('auth.forgot_password_title'),
            'success' => $request->query()['success'] ?? null,
            'reset_url' => $request->query()['reset_url'] ?? null,
            'error' => $request->query()['error'] ?? null,
            'email' => $request->query()['email'] ?? '',
        ]);
    }

    public function sendForgotPassword(Request $request): Response
    {
        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::view('auth/forgot-password', [
                'title' => __('auth.forgot_password_title'),
                'error' => __('errors.invalid_csrf'),
                'email' => (string) $request->input('email', ''),
            ]);
        }

        $email = (string) $request->input('email', '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::view('auth/forgot-password', [
                'title' => __('auth.forgot_password_title'),
                'errors' => ['email' => [__('validation.email_required')]],
                'email' => $email,
            ]);
        }

        $result = $this->passwordResetService->requestReset($email);
        $query = 'success=1';

        if ($result['reset_url'] !== null) {
            $query .= '&reset_url=' . urlencode($result['reset_url']);
        }

        return Response::redirect('/forgot-password?' . $query);
    }

    public function showResetPassword(Request $request): Response
    {
        if ($this->authService->currentUser() !== null) {
            return Response::redirect('/dashboard');
        }

        $token = (string) ($request->query()['token'] ?? '');

        if (!$this->passwordResetService->isTokenValid($token)) {
            return Response::view('auth/forgot-password', [
                'title' => __('auth.forgot_password_title'),
                'error' => __('auth.reset_token_invalid'),
            ]);
        }

        return Response::view('auth/reset-password', [
            'title' => __('auth.reset_password_title'),
            'token' => $token,
            'errors' => [],
            'error' => $request->query()['error'] ?? null,
        ]);
    }

    public function resetPassword(Request $request): Response
    {
        $token = (string) $request->input('token', '');

        if (!verify_csrf_token($request->input('csrf_token'))) {
            return Response::redirect('/reset-password?token=' . urlencode($token) . '&error=csrf');
        }

        try {
            $this->passwordResetService->resetPassword($token, $request->all());
        } catch (ValidationException $e) {
            if (!$this->passwordResetService->isTokenValid($token)) {
                return Response::view('auth/forgot-password', [
                    'title' => __('auth.forgot_password_title'),
                    'error' => __('auth.reset_token_invalid'),
                ]);
            }

            return Response::view('auth/reset-password', [
                'title' => __('auth.reset_password_title'),
                'token' => $token,
                'errors' => $e->errors(),
                'error' => null,
            ]);
        }

        return Response::redirect('/login?success=password_reset');
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout($request->bearerToken());

        if ($request->isApi()) {
            return Response::json([
                'success' => true,
                'message' => __('api.session_invalidated'),
            ]);
        }

        return Response::redirect('/login');
    }

    public function dashboard(Request $request): Response
    {
        $user = $this->authService->currentUser();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $roles = $this->authService->getUserRoles($user->id);
        $overview = $this->learnerDashboardService->buildOverview($user->id);

        return Response::view('dashboard/home', [
            'title' => __('dashboard.title'),
            'user' => $user,
            'roles' => $roles,
            'isAdmin' => in_array('admin', $roles, true),
            'overview' => $overview,
        ]);
    }

    /** @param array<string, array<int, string>> $errors */
    private function respondWithValidationErrors(
        Request $request,
        array $errors,
        string $view,
        array $data = [],
    ): Response {
        if ($request->isApi()) {
            return Response::apiError(
                'VALIDATION_FAILED',
                __('errors.validation_failed'),
                422,
                $errors
            );
        }

        return Response::view("auth/{$view}", [
            'title' => $view === 'login' ? __('auth.sign_in') : __('auth.create_account'),
            'errors' => $errors,
            'form' => $data,
            'email' => $data['email'] ?? '',
        ]);
    }

    /** @param array<string, mixed> $data */
    private function respondWithAuthFailure(Request $request, string $message, array $data): Response
    {
        if ($request->isApi()) {
            return Response::apiError('UNAUTHORIZED', $message, 401);
        }

        return Response::view('auth/login', [
            'title' => __('auth.sign_in'),
            'error' => $message,
            'email' => $data['email'] ?? '',
        ]);
    }
}
